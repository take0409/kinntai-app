FROM php:8.5-fpm

WORKDIR /var/www

RUN apt-get update \
    && apt-get install -y git unzip libzip-dev libicu-dev libonig-dev \
    && docker-php-ext-install pdo_mysql bcmath intl zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && echo "date.timezone=Asia/Tokyo" >> /usr/local/etc/php/conf.d/date_timezone.ini
