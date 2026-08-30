<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FN010〜FN011: メッセージ編集・削除機能
 * 他人のメッセージを操作できてしまわないこと(IDOR)も含めて検証する
 */
class ChatMessageEditDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function createChatWithMessage(): array
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
        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_id' => $buyer->id,
            'message' => '編集前のメッセージ',
        ]);

        return [$buyer, $seller, $item, $chat, $message];
    }

    public function test_sender_can_edit_own_message()
    {
        [$buyer, , $item, , $message] = $this->createChatWithMessage();

        $response = $this->actingAs($buyer)->post("/chat/{$item->id}/update", [
            'message_id' => $message->id,
            'message' => '編集後のメッセージ',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('chat_messages', [
            'id' => $message->id,
            'message' => '編集後のメッセージ',
        ]);
    }

    public function test_other_user_cannot_edit_someone_elses_message()
    {
        [, $seller, $item, , $message] = $this->createChatWithMessage();

        $response = $this->actingAs($seller)->post("/chat/{$item->id}/update", [
            'message_id' => $message->id,
            'message' => '第三者による改ざん',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('chat_messages', [
            'id' => $message->id,
            'message' => '編集前のメッセージ',
        ]);
    }

    public function test_sender_can_delete_own_message()
    {
        [$buyer, , $item, , $message] = $this->createChatWithMessage();

        $response = $this->actingAs($buyer)->post("/chat/{$item->id}/delete", [
            'message_id' => $message->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('chat_messages', ['id' => $message->id]);
    }

    public function test_other_user_cannot_delete_someone_elses_message()
    {
        [, $seller, $item, , $message] = $this->createChatWithMessage();

        $response = $this->actingAs($seller)->post("/chat/{$item->id}/delete", [
            'message_id' => $message->id,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('chat_messages', ['id' => $message->id]);
    }
}
