<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Chat;
use App\Models\ChatNotification;
use App\Models\Rating;

class MypageController extends Controller
{
    // プロフィール画面
    public function mypage(Request $request)
    {
        $user = Auth::user();

        // 取引評価の平均
        $ratings = Rating::where('evaluated_id', '=', $user->id)->get();
        if ($ratings->isNotEmpty()) {
            $ratingScoreSum = 0;
            foreach ($ratings as $rating) {
                $ratingScoreSum += $rating->score;
            }
            $ratingScoreAverage = round($ratingScoreSum / count($ratings));
        } else {
            $ratingScoreAverage = 0;
        }
        $hasRatings = $ratings->isNotEmpty();

        // 取引商品の新規通知数
        $chats = Chat::where('is_completed', '=', false)
            ->where(function ($query) use ($user) {
                $query->where('buyer_id', '=', $user->id)->orWhere('seller_id', '=', $user->id);
            })
            ->get();
        $chatItemIds = $chats->pluck('item_id');
        $chatNotificationCounts = [];
        $chatNotificationCountSum = 0;
        if ($chats->isNotEmpty()) {
            foreach ($chats as $chat) {
                if (ChatNotification::where('receiver_id', '=', $user->id)->where('chat_id', '=', $chat->id)->where('is_read', '=', false)->max('message_count') !== null) {
                    $chatNotificationCounts[$chat->item_id] = ChatNotification::where('receiver_id', '=', $user->id)->where('chat_id', '=', $chat->id)->where('is_read', '=', false)->max('message_count');
                    $chatNotificationCountSum += $chatNotificationCounts[$chat->item_id];
                }
            }
        }

        // 表示する商品
        $isSellItem = FALSE;
        $isBuyItem = FALSE;
        $isTradeItem = FALSE;
        $items = [];
        if ($request->tab == 'sell') {
            $items = Item::where('user_id', '=', $user->id)->get();
            $isSellItem = TRUE;
        } elseif ($request->tab == 'buy') {
            $purchaseItemIds = Purchase::where('user_id', '=', $user->id)->get()->pluck('item_id');
            if ($purchaseItemIds->isNotEmpty()) {
                $items = Item::whereIn('id', $purchaseItemIds)->get();
            }
            $isBuyItem = TRUE;
        } else if ($request->tab == 'trade') {
            if ($chats->isNotEmpty()) {
                $items = Item::join('chats', 'items.id', '=', 'chats.item_id')
                    ->leftJoin('chat_messages', 'chats.id', '=', 'chat_messages.chat_id')
                    ->whereIn('item_id', $chatItemIds)
                    ->where('is_completed', '=', false)
                    ->select('items.*')
                    ->selectRaw('MAX(chat_messages.created_at) as latest_message_at')
                    ->groupBy('items.id')
                    ->orderByDesc('latest_message_at')
                    ->get();
            }
            $isTradeItem = TRUE;
        }

        return view('mypage', compact('user', 'ratingScoreAverage', 'hasRatings', 'chatNotificationCounts', 'chatNotificationCountSum', 'items', 'isSellItem', 'isBuyItem', 'isTradeItem'));
    }
}
