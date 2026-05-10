FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --prefer-dist --optimize-autoloader

FROM php:8.3-fpm-alpine AS runtime

ARG APP_USER=app
ARG APP_UID=1000

RUN apk add --no-cache bash icu-libs oniguruma libzip libpng \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS icu-dev oniguruma-dev libzip-dev libpng-dev postgresql-dev \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath intl mbstring opcache pdo_pgsql zip \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=0'; \
        echo 'opcache.memory_consumption=192'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.jit_buffer_size=64M'; \
        echo 'opcache.jit=tracing'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

RUN { \
        echo 'expose_php=Off'; \
        echo 'memory_limit=256M'; \
    } > /usr/local/etc/php/conf.d/app.ini

RUN addgroup -g ${APP_UID} ${APP_USER} \
    && adduser -D -u ${APP_UID} -G ${APP_USER} -s /bin/bash ${APP_USER}

WORKDIR /var/www/html

COPY --chown=${APP_USER}:${APP_USER} . .
COPY --from=vendor --chown=${APP_USER}:${APP_USER} /app/vendor ./vendor

RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R ${APP_USER}:${APP_USER} storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER ${APP_USER}
EXPOSE 9000
CMD ["php-fpm", "-F"]
