<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyListTest extends TestCase
{
    use RefreshDatabase;

    public function test_liked_item_is_shown_in_mylist()
    {
        $me = User::factory()->create();
        $item = Item::factory()->create(['item_name' => 'いいねした商品']);
        Like::create(['user_id' => $me->id, 'item_id' => $item->id]);

        $response = $this->actingAs($me)->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertSee('いいねした商品');
    }

    public function test_sold_item_in_mylist_shows_sold_label()
    {
        $me = User::factory()->create();
        $item = Item::factory()->sold()->create(['item_name' => '売却済みのいいね商品']);
        Like::create(['user_id' => $me->id, 'item_id' => $item->id]);

        $response = $this->actingAs($me)->get('/?tab=mylist');

        $response->assertSee('売却済みのいいね商品');
        $response->assertSee('Sold');
    }

    public function test_own_item_is_not_shown_in_mylist_even_if_liked()
    {
        $me = User::factory()->create();
        $myItem = Item::factory()->create(['user_id' => $me->id, 'item_name' => '自分の出品']);
        Like::create(['user_id' => $me->id, 'item_id' => $myItem->id]);

        $response = $this->actingAs($me)->get('/?tab=mylist');

        $response->assertDontSee('自分の出品');
    }

    public function test_guest_sees_no_items_in_mylist()
    {
        Item::factory()->create(['item_name' => '誰かの商品']);

        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertDontSee('誰かの商品');
    }
}
