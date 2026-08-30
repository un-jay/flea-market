# flea-market

Laravel製フリマアプリ。プログラミングスクールの卒業課題として制作した、副業応募用ポートフォリオ。

ポートフォリオREADMEの書き方・秘匿情報の扱い等の共通方針は `~/.claude/CLAUDE.md` を参照。

## 構成
- `src/` — Laravelアプリ本体（PHP 8.2 / Laravel 9.52.18）
- ローカル開発：`docker-compose.yml` + `docker/php/Dockerfile`（nginx + PHP-FPM + MySQL 8.0.26）
- 本番デプロイ：ルート `Dockerfile`（Apache + PHP単一コンテナ）を [Render](https://render.com) でビルド、
  DBは [Neon](https://neon.tech)（PostgreSQL）
  - ローカルと本番のDockerfileは別物。ローカル用は触らずに本番用を追加する方針で対応済み。
  - 起動時の処理（設定キャッシュ生成 / `migrate --force` / `storage:link`）は
    `docker/render/entrypoint.sh` に集約。
- 本番用の環境変数一覧は `src/.env.production.example`（値は空のテンプレート、実際の値はRenderの管理画面で入力）

## DB
- ローカル：MySQL（`DB_CONNECTION=mysql` がデフォルト）
- 本番：PostgreSQL（Neon）。`config/database.php` のpgsql接続で `sslmode` をデフォルト `require` にしている
  （Neonの必須要件、`DB_SSLMODE` で上書き可）。
- マイグレーションはMySQL固有記法（ENUM等）を使っていないことを確認済み。今後マイグレーションを
  追加する際もPostgreSQL互換の書き方（Laravel標準のSchema Builder）を使うこと。

## 既知の制約（READMEにも記載済み）
- Renderの無料プランはファイルシステムが永続化されないため、アップロードした商品画像等は
  再起動・再デプロイのたびに消える可能性がある。
- 本番のメールは `MAIL_MAILER=log`。実際の送信はしていない（会員登録のメール認証は動かない）。

## テスト
- `src/tests/Feature/` に案件シートのテストケース一覧（全15項目）・機能要件FN001〜FN016
  （取引チャット・評価）に基づくFeatureテストを整備済み（計71件、`docker-compose exec php bash` →
  `php artisan test` で実行）。
- `phpunit.xml` はSQLite（インメモリ）を使う設定にしてある。ローカルMySQL開発用DBには接続しない
  （RefreshDatabaseで開発データが消えるのを防ぐため）。
- `docker/php/Dockerfile` にテスト用の `pdo_sqlite`・`gd`（`UploadedFile::fake()->image()`用）拡張を
  追加済み。ローカルの通常動作（pdo_mysql）には影響しない。
- `UserFactory`/`ItemFactory` は実際のスキーマ（`user_name`列など）に合わせてある。

## 未対応で気づいている軽微な不具合（優先度低）
- `Item::user()`（app/Models/Item.php）が `belongsTo(Like::class)` になっている（本来は
  `belongsTo(User::class)`）。現状このリレーションはどこからも呼ばれておらず実害なし。
- `ChatController::delete()` が、削除対象メッセージと同じ`created_at`の`ChatNotification`を
  タイムスタンプ一致で探している。外部キーではなくタイムスタンプ一致という設計は本質的に脆い
  （実運用ではほぼ同時に作られるため今のところ問題は出ていない）。
- CSSに `@media` クエリが無く、レスポンシブ対応（PC/タブレット崩れ対策）が意図通り機能しているか
  未検証。

## 注意
- `docker/mysql/data` はローカルMySQLの実データなので `.gitignore` で除外済み。今後もコミットしないこと。
- `.env` はコミットしない（`src/.gitignore` で除外済み）。秘匿情報はハードコーディングしない。
- この作業環境（WSL）には`docker`コマンド・DBドライバが無く、Claude側ではテストやアプリの実行が
  できない。動作確認は必ずユーザー側のDocker環境で行ってもらう。
- git運用は「featureブランチを切る→コミット→push→PR文案を提示→ユーザーがGitHub上でマージ・
  ブランチ削除→ローカルでmainをfast-forward同期→次の作業」を1サイクルとして繰り返している。
