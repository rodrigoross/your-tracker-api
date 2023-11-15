FROM phpswoole/swoole:5.0-php8.2-alpine

ARG GID
ARG UID

ENV GID=${GID}
ENV UID=${UID}

RUN addgroup -g $GID --system laravel
RUN adduser -G laravel --system -D -s /bin/sh -u ${UID} laravel

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS curl-dev openssl-dev pcre-dev pcre2-dev zlib-dev && \
    apk add --no-cache \
    libstdc++ \
    bash \
    su-exec \
    postgresql-dev \
    linux-headers \
    shadow && \
    \
    docker-php-ext-install \
    pcntl \
    pdo \
    pdo_pgsql \
    sockets && \
    pecl install inotify && \
    docker-php-ext-enable inotify && \
    apk del .build-deps

# Configure some extensions
RUN apk add --no-cache libzip-dev
RUN docker-php-ext-configure zip
RUN docker-php-ext-install -j "$(nproc)" zip

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY --from=node:18.16.0-alpine /usr/local/bin /usr/local/bin
COPY --from=node:18.16.0-alpine /usr/local/lib/node_modules /usr/local/lib/node_modules

RUN node --version
RUN npm --version

COPY ./docker/laravel/start-container /usr/local/bin/start-container
COPY ./docker/laravel/php.ini /etc/php/8.2/cli/conf.d/99-sail.ini

COPY composer.json composer.lock /var/www/html/
RUN if [ "$APP_ENV" = "production" ] ; then \
    composer install --no-scripts --no-interaction --optimize-autoloader --no-dev; \
    else \
    composer install --no-scripts --no-interaction; \
    fi

COPY . /var/www/html/
RUN chmod 777 -R /var/www/html/storage /var/www/html/bootstrap/cache

USER laravel
