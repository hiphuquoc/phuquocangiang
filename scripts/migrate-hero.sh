#!/usr/bin/env bash
set -e
cd /var/www/html/superdong.dev
php artisan migrate --force --no-interaction
php artisan route:list --name=admin.homeHero 2>/dev/null | head -10
