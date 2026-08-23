<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>フリマアプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
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
            <form id="purchase-form" action="/purchase/{{$item->id}}/create" method="post">
                @csrf
                <div class="left-container">
                    <div class="under-bar-section">
                        <div class="item-img">
                            <img src="{{ asset($item->item_image) }}" >
                        </div>
                        <div>
                            <div class="item-name">{{ $item->item_name }}</div>
                            <div class="price">￥<span>{{ number_format($item->price) }}</span></div>
                        </div>
                    </div>
                    <div class="under-bar-section">
                        <label class="form-item__ttl">支払い方法</label>
                        <div class="form-item__select">
                            <label class="form-item__select-msg">選択肢を選んでください</label>
                            <div class="form-item__options">
                                <label class="form-item__option"><input type="radio" class="form-item__option-input" name="payment_method" value="1">コンビニ支払い</label>
                                <label class="form-item__option"><input type="radio" class="form-item__option-input" name="payment_method" value="2">カード支払い</label>
                            </div>
                        </div>
                        <div class="form-item__error">
                            @error('payment_method')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                    <div class="under-bar-section">
                        <div class="form-item__header">
                            <label class="form-item__ttl">配送先</label>
                            <a href="/purchase/address/{{$item->id}}">変更する</a>
                        </div>
                        @if (isset($user->postal_code) && isset($user->address) && isset($user->building))
                            <div class="form-item__address">
                                <p>〒<span>{{ $user->postal_code }}</span>&emsp;<span>{{ $user->address }}</span>&emsp;<span>{{ $user->building }}</span></p>
                                <input type="hidden" name="shipping_postal_code" value="{{ $user->postal_code }}">
                                <input type="hidden" name="shipping_address" value="{{ $user->address }}">
                                <input type="hidden" name="shipping_building" value="{{ $user->building }}">
                            </div>
                        @elseif (isset($user->postal_code) && isset($user->address))
                            <div class="form-item__address">
                                <p>〒<span>{{ $user->postal_code }}</span>&emsp;<span>{{ $user->address }}</span></p>
                                <p>{{ $user->address }}</p>
                                <input type="hidden" name="shipping_postal_code" value="{{ $user->postal_code }}">
                                <input type="hidden" name="shipping_address" value="{{ $user->address }}">
                            </div>
                        @else
                            <div class="form-item__address">
                                <p>配送先住所が登録されていません</p>
                                <p>購入前に配送先住所を登録してください</p>
                            </div>
                        @endif
                        <div class="form-item__error">
                            @error('shipping_postal_code')
                                {{ $message }}
                            @enderror
                        <div class="form-item__error">
                        </div>
                            @error('shipping_address')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="right-container">
                    <table>
                        <tr>
                            <td>商品代金</td>
                            <td>￥<span>{{ number_format($item->price) }}</span></td>
                        </tr>
                        <tr>
                            <td>支払い方法</td>
                            <td id="selected__payment-method"></td>
                        </tr>
                    </table>
                    <div class="form__btn">
                        <button class="form__btn-submit" type="submit">購入する</button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        /*========================================
        セレクトボックスの開閉動作
        ========================================*/
        const selects = document.querySelectorAll('.form-item__select');
        // 各セレクトボックスごとの処理
        selects.forEach(select => {
            const dropdown = select.querySelector('.form-item__options');
            const options = dropdown.querySelectorAll('.form-item__option');
            // セレクトボックスをクリックすると開く
            select.addEventListener('click', () => {
                select.classList.add('open');
            });
            // 各オプションごとの処理
            options.forEach(option => {
                const input = option.querySelector('.form-item__option-input');
                // オプション選択時の処理
                option.addEventListener('click', (e) => {
                    const displayContent = select.querySelector('.form-item__select-msg');
                    const selectedPaymentMethod = document.getElementById('selected__payment-method');
                    e.stopPropagation(); // クリックイベントが親要素に伝播しないようにする
                    displayContent.textContent = option.textContent; // 選択された値を表示
                    selectedPaymentMethod.textContent = option.textContent; // 選択された支払い方法を表示
                    console.log(`選択された値: ${input.value}`); // 選択された値を取得
                    select.classList.remove('open'); // ドロップダウンを閉じる
                });
            });
            // ページ外クリックでドロップダウンを閉じる
            document.addEventListener('click', (e) => {
                if (!select.contains(e.target)) {
                    select.classList.remove('open');
                }
            });
        });

        /*========================================
        二重送信防止
        ボタン連打やダブルタップで購入リクエストが複数飛ぶと、
        同じ商品に対して購入レコードが重複作成されてしまうため、
        送信時にボタンを無効化してクライアント側でも抑止する
        （サーバー側の対策はPurchaseController::create()を参照）
        ========================================*/
        const purchaseForm = document.getElementById('purchase-form');
        const purchaseSubmitButton = purchaseForm.querySelector('.form__btn-submit');
        purchaseForm.addEventListener('submit', () => {
            purchaseSubmitButton.disabled = true;
        });
    </script>
</body>
</html>
