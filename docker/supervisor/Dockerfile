FROM php:8.2-fpm-alpine

RUN apk update && apk upgrade && apk add --no-cache supervisor

RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis

RUN docker-php-ext-install pcntl

RUN mkdir -p "/etc/supervisor/logs"

WORKDIR /var/www/html

COPY ./conf.d/* /etc/supervisor/conf.d/
COPY ./supervisor.conf /etc/supervisor/supervisord.conf

ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c",  "/etc/supervisor/supervisord.conf"]
