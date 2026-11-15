FROM dunglas/frankenphp:php8.3-bookworm

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    && docker-php-ext-install \
        intl \
        pgsql \
        pdo_pgsql \
        zip \
        gd \
        xml \
        soap \
        opcache

COPY --chown=www-data:www-data . /app
WORKDIR /app

RUN chmod +x /app/railway-start.sh \
    && mkdir -p /var/www/moodledata \
    && chown www-data:www-data /var/www/moodledata

ENTRYPOINT ["/app/railway-start.sh"]
CMD ["--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
