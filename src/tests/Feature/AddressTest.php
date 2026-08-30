<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_shipping_address()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post("/purchase/address/{$item->id}/upload", [
            'postal_code' => '150-0001',
            'address' => '東京都渋谷区神宮前1-1-1',
            'building' => 'テストビル101',
        ]);

        $response->assertRedirect(route('purchase.create', ['item_id' => $item->id]));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'postal_code' => '150-0001',
            'address' => '東京都渋谷区神宮前1-1-1',
            'building' => 'テストビル101',
        ]);
    }

    public function test_updated_address_is_linked_to_purchase_screen()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user)->post("/purchase/address/{$item->id}/upload", [
            'postal_code' => '150-0001',
            'address' => '東京都渋谷区神宮前1-1-1',
            'building' => 'テストビル101',
        ]);

        $response = $this->actingAs($user)->get("/purchase/{$item->id}");

        $response->assertSee('東京都渋谷区神宮前1-1-1');
        $response->assertSee('value="150-0001"', false);
    }
}
