<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>フリマアプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
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
                        <form class="form" action="/logout" method="post">
                            @csrf
                            <button class="header-nav__btn">ログアウト</button>
                        </form>
                    </li>
                    <li class="header-nav__item"><a href="/mypage?tab=sell">マイページ</a></li>
                    <li class="header-nav__item reversible"><a href="/sell">出品</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="container-top__profile">
                <div class="container-top__profile--img">
                    @isset($user->profile_image)
                        <img src="{{ asset($user->profile_image) }}">
                    @endisset
                    @empty($user->profile_image)
                        <div class="profile-img__unset"></div>
                    @endempty
                </div>
                <div class="container-top__profile--name-rating">
                    <div class="profile-name">{{ $user->user_name }}</div>
                    @if ($hasRatings)
                        <div class="rating">
                            @foreach (range(1, 5) as $i)
                                @if ($i <= $ratingScoreAverage)
                                    <img src="{{ asset('images/star_sharp_yellow.svg') }}" alt="">
                                @else
                                    <img src="{{ asset('images/star_sharp_gray.svg') }}" alt="">
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="container-top__profile--nav">
                    <a href="/mypage/profile">プロフィールを編集</a>
                </div>
            </div>
            <nav class="container-nav">
                <ul class="container-nav__list">
                    <li class="container-nav__item"><a href="/mypage?tab=sell" @class(['red' => $isSellItem])>出品した商品</a></li>
                    <li class="container-nav__item"><a href="/mypage?tab=buy" @class(['red' => $isBuyItem])>購入した商品</a></li>
                    <li class="container-nav__item"><a href="/mypage?tab=trade" @class(['red' => $isTradeItem])>取引中の商品 <label>{{$chatNotificationCountSum}}</label></a></li>
                </ul>
            </nav>
            <div class="container__list">
                @foreach ($items as $item)
                    @if ($isTradeItem)
                        <a class="container__item" href="/chat/{{$item->id}}">
                    @else
                        <a class="container__item" href="/item/{{$item->id}}">
                    @endif
                            <div class="container__item-img">
                                <img src="{{ asset($item->item_image) }}" alt="">
                                @if (request()->fullUrl() === url('/mypage?tab=trade') && isset($chatNotificationCounts[$item->id]) && $chatNotificationCounts[$item->id] > 0)
                                    <label class="label__notification-count">{{ $chatNotificationCounts[$item->id] }}</label>
                                @endif
                            </div>
                            <p class="container__item-name">{{ $item->item_name }}</p>
                        </a>
                @endforeach
            </div>
        </div>
    </main>
</body>
</html>
