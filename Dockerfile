ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-cli

ENV COMPOSER_ALLOW_SUPERUSER 1

VOLUME /app
WORKDIR /app

RUN apt-get update && apt-get install -y \
    zip \
    unzip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer