#!/bin/sh
set -e

# Port substitution for Hugging Face (7860) / Render (10000) / Standard (80)
PORT="${PORT:-7860}"
sed -i "s/PORT_PLACEHOLDER/${PORT}/g" /etc/nginx/http.d/default.conf

# Handle TiDB Cloud SSL CA certificate
if [ -n "$MYSQL_ATTR_SSL_CA" ] && [ ! -f "$MYSQL_ATTR_SSL_CA" ]; then
    echo "Saving raw certificate to /tmp/tidb-ca.pem..."
    printf "%s\n" "$MYSQL_ATTR_SSL_CA" > /tmp/tidb-ca.pem
    export MYSQL_ATTR_SSL_CA="/tmp/tidb-ca.pem"
elif [ -z "$MYSQL_ATTR_SSL_CA" ]; then
    if [ -f "/etc/ssl/certs/isrgrootx1.pem" ]; then
        export MYSQL_ATTR_SSL_CA="/etc/ssl/certs/isrgrootx1.pem"
    elif [ -f "/etc/ssl/certs/ca-certificates.crt" ]; then
        export MYSQL_ATTR_SSL_CA="/etc/ssl/certs/ca-certificates.crt"
    fi
fi

# Ensure storage & runtime directories exist and have proper permissions for any UID
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/lib/nginx/tmp \
         /var/log/nginx

chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/lib/nginx /var/log/nginx /var/run

# Create storage symlink
php artisan storage:link --force || true

# Auto-create database if not exists
if [ -n "$DB_DATABASE" ] && [ "$DB_CONNECTION" = "mysql" ]; then
    php -r "
    try {
        \$host = getenv('DB_HOST') ?: '127.0.0.1';
        \$port = getenv('DB_PORT') ?: 3306;
        \$user = getenv('DB_USERNAME') ?: 'root';
        \$pass = getenv('DB_PASSWORD') ?: '';
        \$db   = getenv('DB_DATABASE') ?: 'pos_cafe';
        \$ca   = getenv('MYSQL_ATTR_SSL_CA');
        \$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        if (\$ca && file_exists(\$ca)) { \$opts[PDO::MYSQL_ATTR_SSL_CA] = \$ca; }
        \$pdo = new PDO(\"mysql:host={\$host};port={\$port}\", \$user, \$pass, \$opts);
        \$pdo->exec(\"CREATE DATABASE IF NOT EXISTS \`{\$db}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\");
        echo \"Database '{\$db}' verified/created successfully on host {\$host}.\n\";
    } catch (Exception \$e) {
        echo \"Database check/create notice: \" . \$e->getMessage() . \"\n\";
    }
    "
fi

# Run database migrations if configured
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations on database: ${DB_DATABASE}..."
    php artisan migrate --force || true
    
    if [ "$RUN_SEEDER" = "true" ]; then
        echo "Running database seeder..."
        php artisan db:seed --force || true
    fi
fi

# Optimize and cache configuration, routes, views, and Filament components
echo "Optimizing application cache and Filament components for high speed..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
php artisan filament:optimize || true
php artisan icons:cache || true

echo "Starting services via supervisord on port ${PORT}..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
