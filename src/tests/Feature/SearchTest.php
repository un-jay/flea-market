<?php

namespace Tests\Feature;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_matches_items_by_partial_keyword()
    {
        Item::factory()->create(['item_name' => 'ワイヤレスイヤホン']);
        Item::factory()->create(['item_name' => '腕時計']);

        $response = $this->get('/search?keyword=' . urlencode('イヤホン'));

        $response->assertStatus(200);
        $response->assertSee('ワイヤレスイヤホン');
        $response->assertDontSee('腕時計');
    }

    public function test_search_keyword_is_retained_in_search_input()
    {
        Item::factory()->create(['item_name' => 'ワイヤレスイヤホン']);

        $response = $this->get('/search?keyword=' . urlencode('イヤホン'));

        $response->assertSee('value="イヤホン"', false);
    }
}
