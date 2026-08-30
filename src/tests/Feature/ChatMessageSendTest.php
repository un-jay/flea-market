<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * FN006〜FN008: 取引チャット投稿・バリデーション・エラーメッセージ
 */
class ChatMessageSendTest extends TestCase
{
    use RefreshDatabase;

    private function createChat(): array
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);
        $chat = Chat::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'item_id' => $item->id,
            'is_completed' => false,
        ]);

        return [$buyer, $seller, $item, $chat];
    }

    public function test_authenticated_user_can_send_message_with_text_only()
    {
        [$buyer, , $item, $chat] = $this->createChat();

        $response = $this->actingAs($buyer)->post("/chat/{$item->id}/create", [
            'message' => 'よろしくお願いします',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('chat_messages', [
            'chat_id' => $chat->id,
            'sender_id' => $buyer->id,
            'message' => 'よろしくお願いします',
        ]);
    }

    public function test_authenticated_user_can_send_message_with_image()
    {
        [$buyer, , $item] = $this->createChat();

        $response = $this->actingAs($buyer)->post("/chat/{$item->id}/create", [
            'message' => '画像付きメッセージ',
            'image' => UploadedFile::fake()->image('photo.jpg'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('chat_messages', ['message' => '画像付きメッセージ']);
    }

    public function test_message_is_required()
    {
        [$buyer, , $item] = $this->createChat();

        $response = $this->actingAs($buyer)->post("/chat/{$item->id}/create", [
            'message' => '',
        ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_message_must_be_400_characters_or_less()
    {
        [$buyer, , $item] = $this->createChat();

        $response = $this->actingAs($buyer)->post("/chat/{$item->id}/create", [
            'message' => str_repeat('あ', 401),
        ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_message_image_must_be_jpeg_or_png()
    {
        [$buyer, , $item] = $this->createChat();

        $response = $this->actingAs($buyer)->post("/chat/{$item->id}/create", [
            'message' => 'テスト',
            'image' => UploadedFile::fake()->create('document.pdf', 10),
        ]);

        $response->assertSessionHasErrors('image');
    }
}
