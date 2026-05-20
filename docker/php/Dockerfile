# syntax=docker/dockerfile:1.7

FROM php:8.3-fpm-alpine AS base

# System deps
RUN apk add --no-cache \
        bash \
        git \
        curl \
        zip unzip \
        libpng-dev libjpeg-turbo-dev freetype-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        postgresql-dev \
        nodejs npm \
        autoconf g++ make linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        intl \
        bcmath \
        opcache \
        pcntl \
        sockets \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make linux-headers \
    && rm -rf /var/cache/apk/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Non-root user matching typical host UID
ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} -S app && adduser -u ${UID} -S app -G app \
    && chown -R app:app /var/www
USER app

EXPOSE 9000
CMD ["php-fpm"]
