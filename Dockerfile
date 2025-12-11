FROM dunglas/frankenphp:php8.4-alpine

# Переменные окружения для продакшена
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV SERVER_NAME=":8080"
ENV FRANKENPHP_DOCUMENT_ROOT="/app/public"

RUN install-php-extensions \
    intl \
    zip \
    opcache \
    apcu \
    Gd

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY ./opcache.ini $PHP_INI_DIR/conf.d/opcache.ini
COPY ./Caddyfile /etc/caddy/Caddyfile

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY ./composer.json ./composer.lock ./symfony.lock ./

WORKDIR /app

COPY . .

RUN composer install \
    --no-dev \
    --no-scripts \
    --no-progress

RUN composer dump-autoload --optimize --classmap-authoritative --no-dev
RUN set -eux; \
    mkdir -p var/cache var/log; \
    chmod -R 777 var;

RUN chmod +x entrypoint.sh

VOLUME [ "/app" ]
# VOLUME [ "caddy_data:/data" ]
# VOLUME [ "caddy_config:/config" ]

EXPOSE 8080
ENTRYPOINT ["/app/entrypoint.sh"]
