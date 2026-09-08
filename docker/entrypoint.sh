#!/bin/sh
set -e

# Port substitution for Render/Cloud platforms
PORT="${PORT:-80}"
sed -i "s/PORT_PLACEHOLDER/${PORT}/g" /etc/nginx/http.d/default.conf

# Ensure storage directories exist and have proper permissions
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage symlink
php artisan storage:link --force || true

# Run database migrations if configured
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || echo "Migration notice: check DB connection"
    
    if [ "$RUN_SEEDER" = "true" ]; then
        echo "Running database seeder..."
        php artisan db:seed --force || echo "Seeder notice: already seeded or error"
    fi
fi

# Clear & cache configuration in production if app key is set
if [ -n "$APP_KEY" ]; then
    echo "Caching configuration and routes..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
fi

echo "Starting services via supervisord on port ${PORT}..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
