#!/bin/sh
set -e

echo "Running migrations..."
php /var/www/html/artisan migrate --force

echo "Caching config and routes..."
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

echo "Creating storage link..."
php /var/www/html/artisan storage:link --force 2>/dev/null || true

echo "Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
