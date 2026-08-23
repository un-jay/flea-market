<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Category;
use App\Models\User;
use App\Models\Comment;
use App\Models\Like;

class ItemController extends Controller
{
    // 商品詳細画面
    public function item($item_id)
    {
        $user = Auth::user();
        $item = Item::findOrFail($item_id);
        $likes = Like::where('item_id', $item_id)->get();
        $is_like = FALSE;
        $comments = Comment::where('item_id', $item_id)->get();
        $commentUsers = array();
        $is_comment = FALSE;

        $categories = $item->categories;
        $categoryNames = array();
        foreach ($categories as $category) {
            array_push($categoryNames, Category::where('id', $category->pivot->category_id)->first()->category_name);
        }

        // 認証ユーザ
        if (isset($user)) {
            foreach ($likes as $like) {
                $is_like = $like->isLike($user->id, $item_id);
            }
            foreach ($comments as $comment) {
                $is_comment = $comment->isComment($user->id, $comment->user_id);
                array_push($commentUsers, array_merge(json_decode(json_encode($comment), true), json_decode(json_encode(User::where('id', $comment->user_id)->first()), true)));
            }
        }
        else {
            foreach ($comments as $comment) {
                array_push($commentUsers, array_merge(json_decode(json_encode($comment), true), json_decode(json_encode(User::where('id', $comment->user_id)->first()), true)));
            }
        }
        $isMyCommentList = [];
        foreach ($commentUsers as $commentUser) {
            $isMyCommentList[$commentUser['user_id']] = $commentUser['user_id'] == $user->id;
        }

        return view('item', compact('user', 'item', 'categoryNames', 'likes', 'is_like', 'commentUsers', 'is_comment', 'isMyCommentList'));
    }

    // コメント機能
    public function comment(CommentRequest $request, Comment $comment)
    {
        $user = Auth::user();
        $data = $request->all();

        $comment->commentStore($user->id, $data);

        return back();
    }

    // いいね追加機能
    public function storeLike(Request $request, Like $like)
    {
        $user = Auth::user();
        $item_id = $request->item_id;
        $like_id = $like->id;
        $is_like = $like->isLike($user->id, $item_id);

        if(!$is_like) {
            $like->likeStore($user->id, $item_id);
            return back();
        }
        return back();
    }

    // いいね削除機能
    public function destroyLike(Request $request, Like $like)
    {
        $user = Auth::user();
        $item_id = $request->item_id;
        $thisLike = Like::where('user_id', $user->id)->where('item_id', $item_id)->first();

        if ($thisLike !== null) {
            $like->likeDestroy($thisLike->id);
        }

        return back();
    }
}
