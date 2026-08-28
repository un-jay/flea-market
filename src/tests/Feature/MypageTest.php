<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MypageTest extends TestCase
{
    use RefreshDatabase;

    public function test_mypage_shows_user_name()
    {
        $user = User::factory()->create(['user_name' => 'テスト太郎']);

        $response = $this->actingAs($user)->get('/mypage');

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
    }

    public function test_mypage_shows_own_listed_items()
    {
        $user = User::factory()->create();
        Item::factory()->create(['user_id' => $user->id, 'item_name' => '出品したギター']);

        $response = $this->actingAs($user)->get('/mypage?tab=sell');

        $response->assertSee('出品したギター');
    }

    public function test_mypage_shows_purchased_items()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['item_name' => '購入したカメラ']);
        Purchase::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'shipping_postal_code' => '106-6118',
            'shipping_address' => '東京都港区六本木6-10-1',
            'shipping_building' => '',
            'payment_method' => '1',
        ]);

        $response = $this->actingAs($user)->get('/mypage?tab=buy');

        $response->assertSee('購入したカメラ');
    }
}
