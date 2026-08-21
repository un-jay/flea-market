<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>フリマアプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/list.css') }}">
</head>

<body>
    <header>
        <div class="header-inner">
            <div class="header__ttl">
                <img src="{{ asset('images/logo.svg') }}" alt="">
            </div>
            <form action="/search" class="search-form" method="get">
                @csrf
                <input type="hidden" name="is_mylist" value="{{ $isMyList }}">
                <input type="search" class="search-form__input" name="keyword" placeholder="なにをお探しですか？" value="{{ old('keyword') ?? request()->keyword }}">
            </form>
            <nav class="header-nav">
                <ul class="header-nav__list">
                    <li class="header-nav__item">
                        @auth
                            <form class="form" action="/logout" method="post">
                                @csrf
                                <button class="header-nav__button">ログアウト</button>
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
            <nav class="container-nav">
                <ul class="container-nav__list">
                    @if (isset(request()->keyword) && request()->keyword !== '')
                        <li class="container-nav__item"><a href="/?keyword={{request()->keyword}}"  @class(['red' => !$isMyList])>おすすめ</a></li>
                        <li class="container-nav__item"><a href="/?tab=mylist&keyword={{request()->keyword}}"  @class(['red' => $isMyList])>マイリスト</a></li>
                    @else
                        <li class="container-nav__item"><a href="/"  @class(['red' => !$isMyList])>おすすめ</a></li>
                        <li class="container-nav__item"><a href="/?tab=mylist"  @class(['red' => $isMyList])>マイリスト</a></li>
                    @endif
                </ul>
            </nav>
            <div class="container__list">
                @isset ($items)
                    @foreach ($items as $item)
                        <a class="container__item" href="/item/{{$item->id}}">
                            <div class="container__item-img">
                                <img src="{{ asset($item->item_image) }}" alt="">
                            </div>
                            @if (!$item->is_sold)
                                <div class="container__item-name">
                                    <label>{{ $item->item_name }}</label>
                                </div>
                            @else
                                <div class="container__item-name is_sold">
                                    <label>{{ $item->item_name }}</label>
                                    <label class="sold-label">Sold</label>
                                </div>
                            @endif
                        </a>
                    @endforeach
                @endisset
            </div>
        </div>
    </main>
</body>
</html>
