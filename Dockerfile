
FROM php:8.2-apache


RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    zlib1g-dev \
    libzip-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_sqlite mbstring zip


COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html