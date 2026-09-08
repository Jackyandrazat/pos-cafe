FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    bash \
    ca-certificates \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    libzip-dev \
    oniguruma-dev

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        bcmath \
        gd \
        intl \
        zip \
        opcache \
        pcntl \
        exif

# Download official Let's Encrypt ISRG Root X1 CA cert for TiDB Cloud
RUN curl -fsSL https://letsencrypt.org/certs/isrgrootx1.pem -o /etc/ssl/certs/isrgrootx1.pem

# Install Composer from official image
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Set permissions
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose ports (7860 for Hugging Face, 10000 for Render, 80 for Standard)
EXPOSE 7860 10000 80

# Run entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
