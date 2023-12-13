FROM php:8.2-fpm-alpine

RUN apk update && apk upgrade && apk add --no-cache supervisor

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS curl-dev openssl-dev pcre-dev pcre2-dev zlib-dev && \
         apk add --no-cache \
         libstdc++ \
         bash \
         su-exec \
         postgresql-dev \
    && pecl install redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis

RUN docker-php-ext-install pcntl \
    pdo \
    pdo_pgsql

RUN mkdir -p "/etc/supervisor/logs"

WORKDIR /var/www/html

COPY ./conf.d/* /etc/supervisor/conf.d/
COPY ./supervisor.conf /etc/supervisor/supervisord.conf

ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c",  "/etc/supervisor/supervisord.conf"]
