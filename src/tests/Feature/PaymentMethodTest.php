<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 支払い方法選択機能。「選択した支払い方法が画面に即時反映される」という
 * 部分はJSのみによる挙動でFeatureテストの対象外(ブラウザでの目視確認が必要)。
 * ここでは選択した支払い方法が購入処理に正しく渡り保存されることのみ検証する。
 */
class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_payment_method_is_saved_on_purchase()
    {
        $buyer = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($buyer)->post("/purchase/{$item->id}/create", [
            'payment_method' => '2',
            'shipping_postal_code' => '106-6118',
            'shipping_address' => '東京都港区六本木6-10-1',
            'shipping_building' => '',
        ]);

        $this->assertDatabaseHas('purchases', [
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'payment_method' => '2',
        ]);
    }
}
