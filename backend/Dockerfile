FROM composer:2 AS vendor
# FROM composer:2-php8.3 AS vendor

WORKDIR /app
COPY . . 
RUN composer install  --no-scripts  --no-interaction --prefer-dist


FROM dunglas/frankenphp:php8.3

# ENV SERVER_NAME=your-domain-name.example.com
# If you want to disable HTTPS, use this value instead:
ENV SERVER_NAME=":8080"

RUN install-php-extensions \
  pgsql \
  pdo_pgsql \
  zip \
  intl \
  gd \
  opcache

# Enable PHP production settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /app

COPY . .

COPY --from=vendor /app/vendor /app/vendor
COPY --from=vendor /app/composer.* /app/

RUN php artisan package:discover --ansi