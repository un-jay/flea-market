<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>フリマアプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/item.css') }}">
</head>

<body>
    <header>
        <div class="header-inner">
            <div class="header__ttl">
                <img src="{{ asset('images/logo.svg') }}" alt="">
            </div>
            <form action="/search" class="search-form" method="get">
                @csrf
                <input type="search" class="search-form__input" name="keyword" placeholder="なにをお探しですか？">
            </form>
            <nav class="header-nav">
                <ul class="header-nav__list">
                    <li class="header-nav__item">
                        @auth
                            <form class="form" action="/logout" method="post">
                                @csrf
                                <button class="header-nav__btn">ログアウト</button>
                            </form>
                        @else
                            <a href="/login">ログイン</a>
                        @endauth
                    </li>
                    <li class="header-nav__item"><a href="/mypage?tab=sell">マイページ</a></li>
                    <li class="header-nav__item reversible"><a href="/sell">出品</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="container-left">
                <div class="item-img">
                    <img src="{{ asset($item->item_image) }}" >
                </div>
            </div>
            <div class="container-right">
                <div class="item-name">{{ $item->item_name }}</div>
                <div class="brand-name">{{ $item->brand_name }}</div>
                <div class="price">￥<span>{{ number_format($item->price) }}</span>（税込）</div>
                <div class="like-comment">
                    <div class="like">
                        @if (!$is_like)
                            <form action="/item/{{$item->id}}/like/store" class="store-like__form" method="post">
                                @csrf
                                <input type="hidden" name="item_id" value="{{ $item->id }}">
                                <button class="like-submit__btn" type="submit">
                                    <img src="{{ asset('images/star.svg') }}" alt="">
                                </button>
                                <p>{{ count($likes) }}</p>
                            </form>
                        @else
                            <form action="/item/{{$item->id}}/like/destroy" class="destroy-like__form" method="post">
                                @csrf
                                <button class="like-submit__btn" type="submit">
                                    <img src="{{ asset('images/star-fill.svg') }}" alt="">
                                </button>
                                <p>{{ count($likes) }}</p>
                            </form>
                        @endif
                    </div>
                    <div class="comment">
                        @if (!$is_comment)
                            <img src="{{ asset('images/comment.svg') }}" alt="">
                        @else
                            <img class="fill" src="{{ asset('images/comment-fill.svg') }}" alt="">
                        @endif
                        <p>{{ count($commentUsers) }}</p>
                    </div>
                </div>
                <div class="nav-purchase">
                    <a href="/purchase/{{$item->id}}">購入手続きはこちらへ</a>
                    <a href="/chat/{{$item->id}}">取引チャットはこちらへ</a>
                </div>
                <div class="description">
                    <label class="description__ttl">商品説明</label>
                    <p>{!! nl2br(htmlspecialchars($item->description)) !!}</p>
                </div>
                <div class="item-data">
                    <label class="item-data__ttl">商品情報</label>
                    <div class="item-data__sub-item">
                        <label class="sub-item__ttl">カテゴリー</label>
                        @foreach ($categoryNames as $categoryName)
                            <label class="sub-item__data category">
                                {{ $categoryName }}
                            </label>
                        @endforeach
                    </div>
                    <div class="item-data__sub-item">
                        <label class="sub-item__ttl">商品の状態</label>
                        @if ($item->status === 1)
                            <label class="sub-item__data">良好</label>
                        @elseif ($item->status === 2)
                            <label class="sub-item__data">目立った傷や汚れなし</label>
                        @elseif ($item->status === 3)
                            <label class="sub-item__data">やや傷や汚れあり</label>
                        @elseif ($item->status === 4)
                            <label class="sub-item__data">状態が悪い</label>
                        @else
                            <label class="sub-item__data">その他</label>
                        @endif
                    </div>
                </div>
                <div class="comment-data">
                    <label class="comment-data__ttl">コメント({{ count($commentUsers) }})</label>
                    @foreach ($commentUsers as $commentUser)
                        <div class="comment-data-set">
                            <div class="comment-data__profile {{ $isMyCommentList[$commentUser['user_id']] ? 'right' : '' }}">
                                <div class="comment-data__profile-img">
                                    @isset($commentUser['profile_image'])
                                        <img src="{{ asset($commentUser['profile_image']) }}">
                                    @endisset
                                    @empty($commentUser['profile_image'])
                                        <div class="profile-img__unset"></div>
                                    @endempty
                                </div>
                                <div class="comment-data__profile-name">
                                    {{ $commentUser['user_name'] }}
                                </div>
                            </div>
                            <div class="comment-data__content {{ $isMyCommentList[$commentUser['user_id']] ? 'right' : '' }}">
                                <label>{!! nl2br(htmlspecialchars($commentUser['content'])) !!}</label>
                            </div>
                        </div>
                    @endforeach
                    <form id="comment-form" action="/item/{{$item->id}}/comment" class="comment-form" method="post">
                        @csrf
                        <label class="form-item__ttl">商品へのコメント</label>
                        <textarea class="form-item__input" name="content" value="{{ old('content') }}"></textarea>
                        <div class="form-item__error">
                            @error('content')
                                {{ $message }}
                            @enderror
                        </div>
                        <div class="form__btn">
                            <input type="hidden" name="item_id" value="{{ $item->id }}">
                            <button class="form__btn-submit" type="submit">コメントを送信する</button>
                        </div>
                    </form>
                </di>
            </div>
        </div>
    </main>

    <script>
        /*========================================
        コメント投稿フォームの二重送信防止（仕様書には無い対応）
        連打で同じコメントが複数投稿されてしまうのを防ぐため、
        送信時にボタンを無効化する
        ========================================*/
        document.addEventListener('DOMContentLoaded', function () {
            const commentForm = document.getElementById('comment-form');
            if (commentForm) {
                commentForm.addEventListener('submit', () => {
                    commentForm.querySelector('.form__btn-submit').disabled = true;
                });
            }
        });
    </script>
</body>
</html>
