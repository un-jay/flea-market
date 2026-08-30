<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ChatMessageRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\CompleteMail;
use App\Models\Item;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\ChatNotification;
use App\Models\Rating;

class ChatController extends Controller
{
    public function index(Request $request, Chat $chatData)
    {
        $chatter = Auth::user();
        $thisChat = Chat::where('item_id', $request->item_id)
            ->where(function ($query) use ($chatter) {
                $query->where('buyer_id', $chatter->id)->orWhere('seller_id', $chatter->id);
            })
            ->first();
        if ($thisChat === null) {
            $sellerId = Item::findOrFail($request->item_id)->user_id;
            $chatData->chatStore($chatter->id, $sellerId, $request->item_id);
            $thisChat = Chat::where('item_id', $request->item_id)
                ->where(function ($query) use ($chatter) {
                    $query->where('buyer_id', $chatter->id)->orWhere('seller_id', $chatter->id);
                })
                ->first();
        }

        $isBuyer = false;
        if ($chatter->id === $thisChat->buyer_id) {
            $partner = User::where('id', $thisChat->seller_id)->first();
            $isBuyer = true;
        } else if ($chatter->id === $thisChat->seller_id) {
            $partner = User::where('id', $thisChat->buyer_id)->first();
            $isBuyer = false;
        }
        $chatMessages = ChatMessage::where('chat_id', $thisChat->id)->get();

        $items = Item::join('chats', 'items.id', '=', 'chats.item_id')
            ->leftJoin('chat_messages', 'chats.id', '=', 'chat_messages.chat_id')
            ->where(function ($query) use ($chatter) {
            $query->where('chats.buyer_id', $chatter->id)->orWhere('chats.seller_id', $chatter->id);
            })
            ->where('chats.is_completed', '!=', true)
            ->select('items.*')
            ->selectRaw('MAX(chat_messages.created_at) as latest_message_at')
            ->groupBy('items.id')
            ->orderByDesc('latest_message_at')
            ->get();
        // $itemsは完了済みの取引を除いたサイドバー表示用の一覧なので、
        // 取引が完了した後に自分のチャット画面を開くと$thisItemがそこに
        // 含まれず取得できなくなってしまう。現在表示中の商品は完了有無に
        // 関わらず取得できるよう、別クエリで取得する
        $thisItem = Item::findOrFail($thisChat->item_id);

        return view('chat', compact(
            'chatter',
            'thisChat',
            'items',
            'thisItem',
            'partner',
            'chatMessages',
            'isBuyer'
        ));
    }

    // 取引チャット機能
    public function create(ChatMessageRequest $request, ChatMessage $chatMessageData, ChatNotification $chatNotificationData)
    {
        $chatter = Auth::user();
        // 表示中のチャット
        $thisChat = Chat::where('item_id', $request->item_id)
            ->where(function ($query) use ($chatter) {
                $query->where('buyer_id', $chatter->id)->orWhere('seller_id', $chatter->id);
            })->first();
        // 取引相手
        if ($chatter->id === $thisChat->buyer_id) {
            $partner = User::where('id', $thisChat->seller_id)->first();
        } else if ($chatter->id === $thisChat->seller_id) {
            $partner = User::where('id', $thisChat->buyer_id)->first();
        }
        $chatMessage = $request->message;
        // 最新のチャット通知
        $latestChatNotification = ChatNotification::where('chat_id', $thisChat->id)->latest()->first();
        $messageCount = 1;
        if (!is_null($latestChatNotification) && $latestChatNotification->receiver_id === $partner->id) {
            // 最新のチャット通知が存在し、通知の受け手が取引相手の場合は、未読メッセージカウントを+1する
            $messageCount = $latestChatNotification->message_count + 1;
        } else {
            // 最新のチャット通知が存在しない、または通知の受け手が取引相手ではない場合は、未読レコードに既読フラグをつける
            while (ChatNotification::where('chat_id', $thisChat->id)->where('receiver_id', $chatter->id)->where('is_read', false)->first() !== null) {
                $tmpChatNotification = ChatNotification::where('chat_id', $thisChat->id)->where('receiver_id', $chatter->id)->where('is_read', false)->first();
                $tmpChatNotification->update([
                    'is_read' => true,
                ]);
                $tmpChatNotification->touch();
            }
        }

        $dir = null;
        $file_name = null;
        if ($request->image !== null) {
            $dir = 'images';
            $file_name = $request->file('image')->getClientOriginalname();
            $request->file('image')->storeAs('public/' . $dir, $file_name);
        }

        $chatMessageData->chatMessageStore($thisChat->id, $chatter->id, $chatMessage, $dir, $file_name);
        $chatNotificationData->chatNotificationStore($thisChat->id, $partner->id, $messageCount);

        return back();
    }

    public function update(ChatMessageRequest $request)
    {
        $thisChatMessage = ChatMessage::where('id', $request->message_id)->first();

        if ($thisChatMessage === null || $thisChatMessage->sender_id !== Auth::id()) {
            abort(403);
        }

        $thisChatMessage->update([
            'message' => $request->message,
        ]);
        $thisChatMessage->touch();

        return back();
    }

    public function delete(Request $request)
    {
        $thisChatMessage = ChatMessage::where('id', $request->message_id)->first();

        if ($thisChatMessage === null || $thisChatMessage->sender_id !== Auth::id()) {
            abort(403);
        }

        $thisChatNotification = ChatNotification::where('created_at', $thisChatMessage->created_at)->first();

        if ($thisChatNotification->is_read === false && $thisChatNotification->message_count > 1) {
            $tmpChatNotification = ChatNotification::where('is_read', false)->first();
            $tmpChatNotification->update([
                'message_count' => $tmpChatNotification->message_count - 1,
            ]);
            $tmpChatNotification->touch();
        }

        $thisChatMessage->delete();
        $thisChatNotification->delete();

        return back();
    }

    public function complete(Request $request, Rating $ratingData)
    {
        $chatter = Auth::user();
        $thisChat = Chat::where('item_id', $request->item_id)
            ->where(function ($query) use ($chatter) {
                $query->where('buyer_id', $chatter->id)->orWhere('seller_id', $chatter->id);
            })->first();
        if ($chatter->id === $thisChat->buyer_id) {
            $partner = User::where('id', $thisChat->seller_id)->first();
        } else if ($chatter->id === $thisChat->seller_id) {
            $partner = User::where('id', $thisChat->buyer_id)->first();
        }

        // 二重送信防止（仕様書には無い対応）：購入処理と同様、連打や通信の遅延・リトライで
        // 同じ相手への評価が複数回登録されてしまう可能性があるため、行ロックを取って
        // 「自分がこの取引を既に評価済みか」を確認してから登録する。
        $alreadyRated = DB::transaction(function () use ($thisChat, $chatter, $partner, $request, $ratingData) {
            $lockedChat = Chat::where('id', $thisChat->id)->lockForUpdate()->first();

            $existingRating = Rating::where('evaluator_id', $chatter->id)
                ->where('item_id', $request->item_id)
                ->exists();

            if ($existingRating) {
                // 既に評価済み（＝直前のリクエストで登録済み）なら何もしない
                return true;
            }

            $ratingData->ratingStore($chatter->id, $partner->id, $request->item_id, $request->score);

            $lockedChat->update([
                'is_completed' => true,
            ]);
            $lockedChat->touch();

            return false;
        });

        if (!$alreadyRated) {
            // 取引完了・評価登録は既に成功しているため、メール送信の失敗が
            // それらを巻き込んで500エラーにならないよう分離する
            try {
                $mailMessage = '取引が完了しました';
                Mail::To($partner->email)->send(new CompleteMail($mailMessage));
            } catch (\Throwable $e) {
                Log::error('取引完了メールの送信に失敗しました', [
                    'chat_id' => $thisChat->id,
                    'to' => $partner->email,
                    'exception' => $e,
                ]);
            }
        }

        return redirect('/');
    }
}
