<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Http\Requests\PurchaseRequest;
use App\Models\Purchase;
use Stripe\Stripe;

class PurchaseController extends Controller
{
    // 商品購入画面
    public function purchase($item_id)
    {
        $user = Auth::user();
        $item = Item::findOrFail($item_id);

        return view('purchase', compact('user', 'item'));
    }

    // 商品購入処理
    public function create($item_id, PurchaseRequest $request, Purchase $purchase_data)
    {
        $user = Auth::user();
        $data = $request->all();

        // 二重送信防止（仕様書には無い対応）：
        // クライアント側でボタンを無効化しているだけでは、連打や通信の遅延・リトライで
        // 購入処理が複数回実行される可能性が残る。行ロックを取って「既に売却済みか」を
        // 確認してから購入処理を行うことで、同じ商品の購入レコードが重複作成されるのを防ぐ。
        DB::transaction(function () use ($user, $item_id, $data, $purchase_data) {
            $item = Item::where('id', $item_id)->lockForUpdate()->firstOrFail();

            if ($item->is_sold) {
                // 既に売却済み（＝直前のリクエストで購入処理済み）なら何もしない
                return;
            }

            $purchase_data->purchaseStore($user->id, $item_id, $data);

            $item->update(['is_sold' => 1]);
            $item->touch();
        });

        return redirect('/mypage?tab=buy');
    }
}
