<?php

namespace Tests\Feature;

use App\Mail\CompleteMail;
use App\Models\Chat;
use App\Models\Item;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * FN012〜FN014, FN016: 取引後評価機能・評価送信後の画面遷移・メール送信
 */
class TransactionCompleteTest extends TestCase
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

    public function test_buyer_can_complete_transaction_and_rate_seller()
    {
        [$buyer, $seller, $item, $chat] = $this->createChat();

        $response = $this->actingAs($buyer)->post("/chat/{$item->id}/complete", [
            'score' => 5,
        ]);

        $this->assertDatabaseHas('ratings', [
            'evaluator_id' => $buyer->id,
            'evaluated_id' => $seller->id,
            'item_id' => $item->id,
            'score' => 5,
        ]);
        $chat->refresh();
        $this->assertTrue((bool) $chat->is_completed);
        $response->assertRedirect('/');
    }

    public function test_seller_cannot_rate_before_buyer_completes_transaction()
    {
        [, $seller, $item] = $this->createChat();

        $response = $this->actingAs($seller)->get("/chat/{$item->id}");

        $response->assertDontSee('id="complete-button"', false);
    }

    public function test_seller_can_rate_buyer_after_buyer_completes_transaction()
    {
        [$buyer, $seller, $item] = $this->createChat();

        $this->actingAs($buyer)->post("/chat/{$item->id}/complete", ['score' => 5]);

        $response = $this->actingAs($seller)->get("/chat/{$item->id}");
        $response->assertSee('id="complete-button"', false);

        $rateResponse = $this->actingAs($seller)->post("/chat/{$item->id}/complete", ['score' => 4]);

        $this->assertDatabaseHas('ratings', [
            'evaluator_id' => $seller->id,
            'evaluated_id' => $buyer->id,
            'item_id' => $item->id,
            'score' => 4,
        ]);
        $rateResponse->assertRedirect('/');
    }

    public function test_rating_redirects_to_item_list()
    {
        [$buyer, , $item] = $this->createChat();

        $response = $this->actingAs($buyer)->post("/chat/{$item->id}/complete", ['score' => 5]);

        $response->assertRedirect('/');
    }

    public function test_rating_is_not_duplicated_when_complete_is_submitted_twice()
    {
        [$buyer, , $item] = $this->createChat();

        $this->actingAs($buyer)->post("/chat/{$item->id}/complete", ['score' => 5]);
        $this->actingAs($buyer)->post("/chat/{$item->id}/complete", ['score' => 3]);

        $this->assertSame(
            1,
            Rating::where('evaluator_id', $buyer->id)->where('item_id', $item->id)->count()
        );
    }

    public function test_completion_sends_mail_to_seller()
    {
        Mail::fake();
        [$buyer, $seller, $item] = $this->createChat();

        $this->actingAs($buyer)->post("/chat/{$item->id}/complete", ['score' => 5]);

        Mail::assertSent(CompleteMail::class, function ($mail) use ($seller) {
            return $mail->hasTo($seller->email);
        });
    }

    public function test_mail_failure_does_not_prevent_transaction_completion()
    {
        // 存在しないメーラーを指定して、メール送信時に例外が発生する状況を再現する
        config(['mail.default' => 'this-mailer-does-not-exist']);

        [$buyer, $seller, $item, $chat] = $this->createChat();

        $response = $this->actingAs($buyer)->post("/chat/{$item->id}/complete", ['score' => 5]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('ratings', [
            'evaluator_id' => $buyer->id,
            'evaluated_id' => $seller->id,
            'item_id' => $item->id,
        ]);
        $chat->refresh();
        $this->assertTrue((bool) $chat->is_completed);
    }
}
