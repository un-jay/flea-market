#!/bin/sh
set -e

# RenderはPORT環境変数でリッスンすべきポートを指定してくる（未設定時は10000を使う）
PORT="${PORT:-10000}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

# TODO: ダミーデータ投入用の一時措置。投入確認後にこの1行は削除すること。
php artisan db:seed --force || true

if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link
fi

exec apache2-foreground
