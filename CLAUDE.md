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

## 注意
- `docker/mysql/data` はローカルMySQLの実データなので `.gitignore` で除外済み。今後もコミットしないこと。
- `.env` はコミットしない（`src/.gitignore` で除外済み）。秘匿情報はハードコーディングしない。
