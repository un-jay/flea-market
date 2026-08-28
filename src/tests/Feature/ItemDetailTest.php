<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_detail_shows_required_information()
    {
        $item = Item::factory()->create([
            'item_name' => '腕時計',
            'brand_name' => 'Rolex',
            'price' => 15000,
            'description' => 'スタイリッシュなデザインのメンズ腕時計',
            'status' => 1,
        ]);

        $response = $this->get('/item/' . $item->id);

        $response->assertStatus(200);
        $response->assertSee('腕時計');
        $response->assertSee('Rolex');
        $response->assertSee('15,000');
        $response->assertSee('スタイリッシュなデザインのメンズ腕時計');
        $response->assertSee('良好');
    }

    public function test_item_detail_shows_multiple_categories()
    {
        $item = Item::factory()->create();
        $categoryA = Category::create(['category_name' => 'メンズ']);
        $categoryB = Category::create(['category_name' => 'アクセサリー']);
        $item->categories()->attach([$categoryA->id, $categoryB->id]);

        $response = $this->get('/item/' . $item->id);

        $response->assertSee('メンズ');
        $response->assertSee('アクセサリー');
    }
}
