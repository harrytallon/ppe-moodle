FROM dunglas/frankenphp:php8.3-bookworm

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libexif-dev \
    git \
    unzip \
    && docker-php-ext-install \
        intl \
        pgsql \
        pdo_pgsql \
        zip \
        gd \
        xml \
        soap \
        opcache \
        exif

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY moodle.ini /usr/local/etc/php/conf.d/zz-moodle.ini

COPY --chown=www-data:www-data . /app
WORKDIR /app

RUN composer install --no-dev --classmap-authoritative --no-interaction

RUN chmod +x /app/railway-start.sh \
    && mkdir -p /var/www/moodledata \
    && chown www-data:www-data /var/www/moodledata

ENTRYPOINT ["/app/railway-start.sh"]
CMD ["--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
