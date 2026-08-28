<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemListTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_all_items()
    {
        Item::factory()->create(['item_name' => '商品A']);
        Item::factory()->create(['item_name' => '商品B']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('商品A');
        $response->assertSee('商品B');
    }

    public function test_sold_item_shows_sold_label()
    {
        Item::factory()->sold()->create(['item_name' => '売却済み商品']);

        $response = $this->get('/');

        $response->assertSee('売却済み商品');
        $response->assertSee('Sold');
    }

    public function test_own_items_are_hidden_from_list_when_authenticated()
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        Item::factory()->create(['user_id' => $me->id, 'item_name' => '自分の出品']);
        Item::factory()->create(['user_id' => $other->id, 'item_name' => '他人の出品']);

        $response = $this->actingAs($me)->get('/');

        $response->assertDontSee('自分の出品');
        $response->assertSee('他人の出品');
    }
}
