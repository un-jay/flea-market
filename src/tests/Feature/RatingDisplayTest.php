<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * US002: 評価平均確認機能
 */
class RatingDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_rating_average_is_displayed_and_rounded()
    {
        $seller = User::factory()->create();
        $buyer1 = User::factory()->create();
        $buyer2 = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);

        // 4と5の平均は4.5 -> 四捨五入で5になる
        Rating::create(['evaluator_id' => $buyer1->id, 'evaluated_id' => $seller->id, 'item_id' => $item->id, 'score' => 4]);
        Rating::create(['evaluator_id' => $buyer2->id, 'evaluated_id' => $seller->id, 'item_id' => $item->id, 'score' => 5]);

        $response = $this->actingAs($seller)->get('/mypage');
        $content = $response->getContent();

        $this->assertSame(5, substr_count($content, 'star_sharp_yellow.svg'));
        $this->assertSame(0, substr_count($content, 'star_sharp_gray.svg'));
    }

    public function test_user_without_ratings_does_not_show_rating_section()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/mypage');

        $response->assertDontSee('star_sharp_yellow.svg');
        $response->assertDontSee('star_sharp_gray.svg');
    }
}
