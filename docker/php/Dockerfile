# docker/php/Dockerfile
FROM php:8.3-fpm-alpine

# Outils + extensions nécessaires à Composer ET à ton app
RUN apk add --no-cache bash git unzip icu-dev oniguruma-dev libzip-dev \
 && docker-php-ext-configure intl \
 && docker-php-ext-install -j$(nproc) intl pdo_mysql zip opcache

# OPcache (prod)
RUN { \
  echo 'opcache.enable=1'; \
  echo 'opcache.validate_timestamps=0'; \
  echo 'opcache.memory_consumption=256'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Installer le binaire Composer dans cette image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# Étape 1: installer les vendors (sans scripts) avec les mêmes extensions que le runtime
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# Étape 2: copier le reste du code (assets, bin, config, public, src, …)
COPY . .

# Entrypoint
COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Droits
RUN addgroup -g 1000 app && adduser -D -G app -u 1000 app \
 && chown -R app:app var public
USER app

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
