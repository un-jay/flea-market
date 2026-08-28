<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_like_item()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post("/item/{$item->id}/like/store", [
            'item_id' => $item->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_liked_item_shows_filled_star_icon()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        Like::create(['user_id' => $user->id, 'item_id' => $item->id]);

        $response = $this->actingAs($user)->get("/item/{$item->id}");

        $response->assertSee('star-fill.svg');
        $response->assertDontSee('star.svg');
    }

    public function test_not_liked_item_shows_outline_star_icon()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->get("/item/{$item->id}");

        $response->assertSee('star.svg');
        $response->assertDontSee('star-fill.svg');
    }

    public function test_authenticated_user_can_unlike_item()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        Like::create(['user_id' => $user->id, 'item_id' => $item->id]);

        $response = $this->actingAs($user)->post("/item/{$item->id}/like/destroy", [
            'item_id' => $item->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }
}
