#!/bin/sh
set -e
 
# Siapkan .env dari contoh jika belum ada
if [ ! -f .env ]; then
  cp .env.example .env
fi
 
# Buat APP_KEY jika belum disediakan lewat environment atau .env
if [ -z "$APP_KEY" ] && ! grep -q '^APP_KEY=.\+' .env; then
  php artisan key:generate --force
fi
 
# Database baru siap setelah migrate
php artisan migrate --force
 
exec php -S 0.0.0.0:3003 -t public public/index.php
