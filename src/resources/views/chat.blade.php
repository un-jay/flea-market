<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>フリマアプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
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
        <div class="side-container">
            <div class="side-container__title">その他の取引</div>
            @foreach ($items as $item)
                <div class="side-container__chat-nav">
                    <a href="/chat/{{$item->id}}">{{ $item->item_name }}</a>
                </div>
            @endforeach
        </div>
        <div class="main-container">
            <div class="main-container__header">
                <div class="main-container__header-title">
                    <div class="main-container__header-title--img">
                        @isset($partner->profile_image)
                            <img src="{{ asset($partner->profile_image) }}">
                        @endisset
                        @empty($partner->profile_image)
                            <div class="profile-img__unset"></div>
                        @endempty
                    </div>
                    <div class="main-container__header-title--name">
                        <div class="profile-name">「{{ $partner->user_name }}」さんとの取引画面</div>
                    </div>
                </div>
                <div class="main-container__header-nav">
                    @if ($isBuyer || $thisChat->is_completed)
                        <button class="main-container__header-nav--btn" id="complete-button">取引完了する</button>
                        <div class="main-container__complete-modal" id="complete-modal">
                            <div class="main-container__complete-modal--content" id="modal-content">
                                <div class="main-container__complete-modal--content-title">
                                    取引が完了しました。
                                </div>
                                <form action="/chat/{{$thisItem->id}}/complete" method="POST">
                                    @csrf
                                    <div id="star-rating" class="star-rating">
                                        <div class="star-rating__title">今回の取引はどうでしたか？</div>
                                        @foreach (range(1, 5) as $i)
                                            <img src="{{ asset('images/star_sharp_gray.svg') }}" class="star" data-value="{{ $i }}">
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="score" id="rating" value="0">
                                    <div class="form__btn">
                                        <button class="form__btn-submit" type="submit">送信する</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="main-container__item-data">
                <div class="item-img">
                    <img src="{{ asset($thisItem->item_image) }}" alt="">
                </div>
                <div class="item-text-data">
                    <div class="item-name">{{ $thisItem->item_name }}</div>
                    <div class="price">￥<span>{{ number_format($thisItem->price) }}</span>（税込）</div>
                </div>
            </div>
            <div class="main-container__chat">
                @foreach ($chatMessages as $chatMessage)
                    <div class="comment-data-set @if($loop->last) last-data-set @endif">
                        @if ($chatMessage->sender_id == $chatter->id)
                            <div class="comment-data__profile right">
                                <div class="comment-data__profile-name">
                                    {{ $chatter->user_name }}
                                </div>
                                <div class="comment-data__profile-img">
                                    @isset($chatter->profile_image)
                                        <img src="{{ asset($chatter->profile_image) }}">
                                    @endisset
                                    @empty($chatter->profile_image)
                                        <div class="profile-img__unset"></div>
                                    @endempty
                                </div>
                            </div>
                            <div class="comment-data__content right" id="view-message-{{ $chatMessage->id }}">
                                @if ($chatMessage->image !== null)
                                    <div>
                                        <img src="{{ asset($chatMessage->image) }}">
                                    </div>
                                @endif
                                <div>{!! nl2br(htmlspecialchars($chatMessage->message)) !!}</div>
                            </div>
                            <div class="comment-data__content right" id="edit-form-{{ $chatMessage->id }}" style="display: none;">
                                @if ($chatMessage->image !== null)
                                    <div>
                                        <img src="{{ asset($chatMessage->image) }}">
                                    </div>
                                @endif
                                <form action="/chat/{{ $thisItem->id }}/update" class="update-form" method="post">
                                    @csrf
                                    <input type="hidden" name="message_id" value="{{ $chatMessage->id }}">
                                    <textarea class="form__textarea" name="message">{{ $chatMessage->message }}</textarea>
                                    <div class="form__btn">
                                        <button type="submit" class="form__btn-submit">保存</button>
                                        <button type="button" class="form__btn-cancel" onclick="cancelEdit({{ $chatMessage->id }})">キャンセル</button>
                                    </div>
                                </form>
                            </div>
                            <div class="comment-data__control right">
                                <button type="button" onclick="showEditForm({{ $chatMessage->id }})" class="form__btn-submit">編集</button>
                                <form action="/chat/{{ $thisItem->id }}/delete" class="delete-form" method="post">
                                    @csrf
                                    <div class="form__btn">
                                        <input type="hidden" name="message_id" value="{{ $chatMessage->id }}">
                                        <button class="form__btn-submit" type="submit">削除</button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="comment-data__profile">
                                <div class="comment-data__profile-img">
                                    @isset($partner->profile_image)
                                        <img src="{{ asset($partner->profile_image) }}">
                                    @endisset
                                    @empty($partner->profile_image)
                                        <div class="profile-img__unset"></div>
                                    @endempty
                                </div>
                                <div class="comment-data__profile-name">
                                    {{ $partner->user_name }}
                                </div>
                            </div>
                            <div class="comment-data__content">
                                @if ($chatMessage->image !== null)
                                    <img src="{{ asset($chatMessage->image) }}">
                                @endif
                                <div>{!! nl2br(htmlspecialchars($chatMessage->message)) !!}</div>
                            </div>
                        @endif
                    </div>
                @endforeach
                <form action="/chat/{{ $thisItem->id }}/create" class="create-form" method="post" enctype="multipart/form-data">
                    @csrf
                    <textarea id="chatInput" class="form-item__textarea" name="message" value="{{ old('message') }}"></textarea>
                    <div class="form-item__img" id="preview">
                        <label for="item_img" class="form-item__img-msg" id="btn_add-figure">
                            画像を追加
                            <input type="file" id="item_img" class="form-item__input" name="image">
                        </label>
                    </div>
                    <div class="form__btn">
                        <button class="form__btn-submit" type="submit">
                            <img src="{{ asset('images/send_button.svg') }}" alt="">
                        </button>
                    </div>
                </form>
                <div class="form-item__error">
                    @error('message')
                        {{ $message }}
                    @enderror
                </div>
                <div class="form-item__error">
                    @error('image')
                        {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
    </main>

    <script>
        /*========================================
        画像のプレビュー表示
        ========================================*/
        // <input>でファイルが選択されたときの処理
        const fileInput = document.getElementById('item_img');
        // ファイル選択時に呼び出す関数
        fileInput.addEventListener('change', () => {
            const files = fileInput.files;
            if (0 < files.length) {
                previewFile(files[0]);
            }
        });
        // ファイル選択時にプレビュー画像を表示する処理
        function previewFile(file) {
            // プレビュー画像を追加する要素
            const preview = document.getElementById('preview');
            const btnAddFigure = document.getElementById('btn_add-figure');
            // プレビュー画像がすでにある場合は削除（innerHTMLを使わない）
            const existingImg = preview.querySelector('img');
            if (existingImg) {
                existingImg.remove();
            }
            // FileReaderオブジェクトを作成
            const reader = new FileReader();
            // ファイルが読み込まれたときに実行する
            reader.onload = function (e) {
                const imageUrl = e.target.result; // 画像のURLはevent.target.resultで呼び出せる
                const img = document.createElement("img"); // img要素を作成
                img.src = imageUrl; // 画像のURLをimg要素にセット
                img.style.cursor = 'pointer';
                preview.appendChild(img); // #previewの中に追加
                btnAddFigure.style.display = 'none'; // 画像が表示されたらborderを消す
                // 画像をクリックすると再度ファイル選択ダイアログを開く
                img.addEventListener('click', () => {
                    fileInput.click();
                });
            }
            // いざファイルを読み込む
            reader.readAsDataURL(file);
        }

        /*========================================
        編集フォーム切り替え
        ========================================*/
        function showEditForm(messageId) {
            const view = document.getElementById('view-message-' + messageId);
            const form = document.getElementById('edit-form-' + messageId);
            if (view && form) {
                view.style.display = 'none';
                form.style.display = 'block';
            }
        }

        function cancelEdit(messageId) {
            const view = document.getElementById('view-message-' + messageId);
            const form = document.getElementById('edit-form-' + messageId);
            if (view && form) {
                form.style.display = 'none';
                view.style.display = 'block';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            /*========================================
            取引完了モーダル
            ========================================*/
            const completeBtn = document.getElementById('complete-button');
            const completeModal = document.getElementById('complete-modal');
            const modalContent = document.getElementById('modal-content');
            const stars = document.querySelectorAll('.star');
            const ratingInput = document.getElementById('rating');

            if (completeBtn && completeModal && modalContent) {
                // モーダル表示
                completeBtn.addEventListener('click', () => {
                    completeModal.style.display = 'flex';
                });

                // モーダル外をクリックしたら閉じる
                completeModal.addEventListener('click', (event) => {
                    if (!modalContent.contains(event.target)) {
                        completeModal.style.display = 'none';
                    }
                });
            }

            // 星の評価処理
            stars.forEach((star, index) => {
                star.addEventListener('click', () => {
                    const rating = index + 1;
                    ratingInput.value = rating;

                    stars.forEach((s, i) => {
                        if (i < rating) {
                            s.src = '{{ asset("images/star_sharp_yellow.svg") }}';
                        } else {
                            s.src = '{{ asset("images/star_sharp_gray.svg") }}';
                        }
                    });
                });
            });

            /*========================================
            入力情報保持
            ========================================*/
            const inputKey = 'chat_message_draft_{{ $thisChat->id ?? 'default' }}'; // 保存キー
            const inputField = document.getElementById('chatInput');

            // 入力内容を復元
            if (localStorage.getItem(inputKey)) {
                inputField.value = localStorage.getItem(inputKey);
            }

            // 入力が変更されるたびに保存
            inputField.addEventListener('input', () => {
                localStorage.setItem(inputKey, inputField.value);
            });

            // フォーム送信時に削除
            inputField.form.addEventListener('submit', () => {
                localStorage.removeItem(inputKey);
            });
        });
    </script>
</body>
</html>
