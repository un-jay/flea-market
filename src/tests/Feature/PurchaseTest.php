<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_purchase_item()
    {
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['is_sold' => false]);

        $response = $this->actingAs($buyer)->post("/purchase/{$item->id}/create", [
            'payment_method' => '1',
            'shipping_postal_code' => '106-6118',
            'shipping_address' => '東京都港区六本木6-10-1',
            'shipping_building' => '六本木ヒルズ森タワー',
        ]);

        $response->assertRedirect('/mypage?tab=buy');
        $this->assertDatabaseHas('purchases', [
            'user_id' => $buyer->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_purchased_item_is_marked_as_sold()
    {
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['is_sold' => false]);

        $this->actingAs($buyer)->post("/purchase/{$item->id}/create", [
            'payment_method' => '1',
            'shipping_postal_code' => '106-6118',
            'shipping_address' => '東京都港区六本木6-10-1',
            'shipping_building' => '',
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'is_sold' => true,
        ]);
    }

    public function test_purchase_history_is_added_to_mypage()
    {
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['item_name' => '購入履歴テスト商品']);

        $this->actingAs($buyer)->post("/purchase/{$item->id}/create", [
            'payment_method' => '1',
            'shipping_postal_code' => '106-6118',
            'shipping_address' => '東京都港区六本木6-10-1',
            'shipping_building' => '',
        ]);

        $response = $this->actingAs($buyer)->get('/mypage?tab=buy');

        $response->assertSee('購入履歴テスト商品');
    }
}
