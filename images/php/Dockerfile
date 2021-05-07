FROM php:7.4-fpm-alpine

RUN apk add --update --no-cache \
    libressl-dev \
    zlib-dev && \
    apk add --update --no-cache --virtual buildDeps \ 
    autoconf \
    gcc \
    make \
    g++ && \
    pecl install mongodb && \
    apk del buildDeps && \
    docker-php-ext-enable mongodb
