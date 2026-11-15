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

COPY . /app
WORKDIR /app

# Ensure web server can write configuration and installer files
RUN mkdir -p /var/www/moodledata \
    && chown -R www-data:www-data /app /var/www/moodledata
