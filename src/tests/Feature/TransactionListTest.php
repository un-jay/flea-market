<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatNotification;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FN001〜FN005: 取引中商品確認・遷移・自動ソート・新規通知確認
 */
class TransactionListTest extends TestCase
{
    use RefreshDatabase;

    public function test_mypage_trade_tab_shows_ongoing_chat_items()
    {
        $me = User::factory()->create();
        $partner = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $partner->id, 'item_name' => '取引中の商品']);
        $chat = Chat::create([
            'buyer_id' => $me->id,
            'seller_id' => $partner->id,
            'item_id' => $item->id,
            'is_completed' => false,
        ]);
        ChatMessage::create(['chat_id' => $chat->id, 'sender_id' => $partner->id, 'message' => 'よろしくお願いします']);

        $response = $this->actingAs($me)->get('/mypage?tab=trade');

        $response->assertSee('取引中の商品');
        $response->assertSee('/chat/' . $item->id, false);
    }

    public function test_completed_chat_is_not_shown_in_trade_tab()
    {
        $me = User::factory()->create();
        $partner = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $partner->id, 'item_name' => '完了済みの取引']);
        $chat = Chat::create([
            'buyer_id' => $me->id,
            'seller_id' => $partner->id,
            'item_id' => $item->id,
            'is_completed' => true,
        ]);
        ChatMessage::create(['chat_id' => $chat->id, 'sender_id' => $partner->id, 'message' => 'ありがとうございました']);

        $response = $this->actingAs($me)->get('/mypage?tab=trade');

        $response->assertDontSee('完了済みの取引');
    }

    public function test_trade_items_are_sorted_by_latest_message_first()
    {
        $me = User::factory()->create();
        $partner = User::factory()->create();

        $itemOld = Item::factory()->create(['user_id' => $partner->id, 'item_name' => '古いメッセージの商品']);
        $chatOld = Chat::create([
            'buyer_id' => $me->id, 'seller_id' => $partner->id, 'item_id' => $itemOld->id, 'is_completed' => false,
        ]);
        $oldMessage = ChatMessage::create(['chat_id' => $chatOld->id, 'sender_id' => $partner->id, 'message' => '2日前です']);
        $oldMessage->created_at = now()->subDays(2);
        $oldMessage->save();

        $itemNew = Item::factory()->create(['user_id' => $partner->id, 'item_name' => '新しいメッセージの商品']);
        $chatNew = Chat::create([
            'buyer_id' => $me->id, 'seller_id' => $partner->id, 'item_id' => $itemNew->id, 'is_completed' => false,
        ]);
        ChatMessage::create(['chat_id' => $chatNew->id, 'sender_id' => $partner->id, 'message' => 'たった今です']);

        $response = $this->actingAs($me)->get('/mypage?tab=trade');
        $content = $response->getContent();

        $posNew = strpos($content, '新しいメッセージの商品');
        $posOld = strpos($content, '古いメッセージの商品');

        $this->assertNotFalse($posNew);
        $this->assertNotFalse($posOld);
        $this->assertLessThan($posOld, $posNew);
    }

    public function test_item_with_unread_messages_shows_notification_count_badge()
    {
        $me = User::factory()->create();
        $partner = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $partner->id]);
        $chat = Chat::create([
            'buyer_id' => $me->id, 'seller_id' => $partner->id, 'item_id' => $item->id, 'is_completed' => false,
        ]);
        ChatNotification::create([
            'chat_id' => $chat->id, 'receiver_id' => $me->id, 'message_count' => 3, 'is_read' => false,
        ]);

        $response = $this->actingAs($me)->get('/mypage?tab=trade');

        $response->assertSee('label__notification-count">3</label>', false);
    }

    public function test_item_without_unread_messages_shows_no_notification_badge()
    {
        $me = User::factory()->create();
        $partner = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $partner->id]);
        $chat = Chat::create([
            'buyer_id' => $me->id, 'seller_id' => $partner->id, 'item_id' => $item->id, 'is_completed' => false,
        ]);
        ChatNotification::create([
            'chat_id' => $chat->id, 'receiver_id' => $me->id, 'message_count' => 1, 'is_read' => true,
        ]);

        $response = $this->actingAs($me)->get('/mypage?tab=trade');

        $response->assertDontSee('label__notification-count', false);
    }

    public function test_trade_tab_label_shows_total_unread_count()
    {
        $me = User::factory()->create();
        $partner = User::factory()->create();

        $itemA = Item::factory()->create(['user_id' => $partner->id]);
        $chatA = Chat::create([
            'buyer_id' => $me->id, 'seller_id' => $partner->id, 'item_id' => $itemA->id, 'is_completed' => false,
        ]);
        ChatNotification::create(['chat_id' => $chatA->id, 'receiver_id' => $me->id, 'message_count' => 2, 'is_read' => false]);

        $itemB = Item::factory()->create(['user_id' => $partner->id]);
        $chatB = Chat::create([
            'buyer_id' => $me->id, 'seller_id' => $partner->id, 'item_id' => $itemB->id, 'is_completed' => false,
        ]);
        ChatNotification::create(['chat_id' => $chatB->id, 'receiver_id' => $me->id, 'message_count' => 3, 'is_read' => false]);

        $response = $this->actingAs($me)->get('/mypage?tab=trade');

        $response->assertSee('取引中の商品 <label>5</label>', false);
    }

    public function test_chat_screen_sidebar_links_to_other_ongoing_transactions()
    {
        $me = User::factory()->create();
        $partner = User::factory()->create();

        $itemA = Item::factory()->create(['user_id' => $partner->id, 'item_name' => '現在見ている商品']);
        $chatA = Chat::create([
            'buyer_id' => $me->id, 'seller_id' => $partner->id, 'item_id' => $itemA->id, 'is_completed' => false,
        ]);
        ChatMessage::create(['chat_id' => $chatA->id, 'sender_id' => $partner->id, 'message' => 'こんにちは']);

        $itemB = Item::factory()->create(['user_id' => $partner->id, 'item_name' => '別の取引商品']);
        $chatB = Chat::create([
            'buyer_id' => $me->id, 'seller_id' => $partner->id, 'item_id' => $itemB->id, 'is_completed' => false,
        ]);
        ChatMessage::create(['chat_id' => $chatB->id, 'sender_id' => $partner->id, 'message' => 'こんにちは2']);

        $response = $this->actingAs($me)->get('/chat/' . $itemA->id);

        $response->assertSee('別の取引商品');
        $response->assertSee('/chat/' . $itemB->id, false);
    }
}
