FROM php:7.4-fpm-alpine

RUN apk add --update --no-cache \
    libressl-dev \
    zlib-dev \
    postgresql-dev \
    nginx && \
    apk add --update --no-cache --virtual buildDeps \
    autoconf \
    gcc \
    make \
    libxml2-dev \
    curl \
    tzdata \
    curl-dev \
    oniguruma-dev \
    g++ && \
    pecl install mongodb && \
    docker-php-ext-install \
    pgsql \
    xml \
    simplexml \
    curl \
    pdo \
    pdo_pgsql \
    mysqli \
    mbstring \
    json \
    sockets \
    posix \
    bcmath && \
    apk del buildDeps && \
    docker-php-ext-enable mongodb
