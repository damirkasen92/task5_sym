#!/bin/sh
set -e  # остановить скрипт при любой ошибке

echo "Очистка кэша..."
php bin/console cache:clear --no-warmup
php bin/console cache:warmup

echo "Установка ассетов..."
php bin/console importmap:install --no-interaction
php bin/console importmap:dump --no-interaction
php bin/console assets:install --no-interaction
php bin/console asset-map:compile

echo "Запуск FrankenPHP..."
exec frankenphp run /etc/caddy/Caddyfile
