<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SellTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_item_with_category_status_name_description_price()
    {
        $user = User::factory()->create();
        $category = Category::create(['category_name' => '家電']);

        $response = $this->actingAs($user)->post('/sell/create', [
            'item_name' => 'テスト商品',
            'description' => 'テスト説明文',
            'price' => 5000,
            'brand_name' => 'Anker',
            'status' => '1',
            'categories' => [$category->id],
            'item_image' => UploadedFile::fake()->image('item.jpg'),
        ]);

        $response->assertRedirect('/mypage?tab=sell');
        $this->assertDatabaseHas('items', [
            'user_id' => $user->id,
            'item_name' => 'テスト商品',
            'description' => 'テスト説明文',
            'price' => 5000,
            'status' => 1,
        ]);

        $item = Item::where('item_name', 'テスト商品')->first();
        $this->assertTrue($item->categories->contains($category->id));
    }

    public function test_item_name_description_price_category_status_image_are_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/sell/create', []);

        $response->assertSessionHasErrors([
            'item_name', 'description', 'price', 'categories', 'status', 'item_image',
        ]);
    }

    public function test_guest_cannot_access_sell_page()
    {
        $response = $this->get('/sell');

        $response->assertRedirect('/login');
    }
}
