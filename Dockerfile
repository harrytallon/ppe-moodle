FROM dunglas/frankenphp:php8.2-bookworm

# Install required system dependencies and PHP extensions for Moodle
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

# Copy Moodle source files into the container web root
COPY . /app
WORKDIR /app
