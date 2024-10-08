# Use Debian Slim as base image
FROM debian:bullseye-slim

# Set environment variables ensure fixed version
ENV NGINX_VERSION 1.18.0
ENV PHP_VERSION 7.3

# Add the Sury repository to get PHP 7.3
RUN apt-get update && apt-get install -y lsb-release ca-certificates apt-transport-https wget gnupg2 && \
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/sury-php.list && \
    wget -qO /etc/apt/trusted.gpg.d/sury-php.gpg https://packages.sury.org/php/apt.gpg

# Install dependencies and PHP extensions
RUN apt-get update && apt-get install --no-install-recommends -y \
    nginx=${NGINX_VERSION}* \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-bz2 \
    php${PHP_VERSION}-calendar \
    php${PHP_VERSION}-dba \
    php${PHP_VERSION}-exif \
    php${PHP_VERSION}-fileinfo \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-gettext \
    php${PHP_VERSION}-imap \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-pdo \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-soap \
    php${PHP_VERSION}-tidy \
    php${PHP_VERSION}-xmlrpc \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-xsl \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-sockets \
    cron g++ gettext libicu-dev openssl \
    vim unzip zip curl optipng jpegoptim pngquant imagemagick \
    libc-client-dev libkrb5-dev libxml2-dev libfreetype6-dev \
    libgd-dev libmcrypt-dev bzip2 libbz2-dev libtidy-dev \
    libcurl4-openssl-dev libz-dev libmemcached-dev libxslt-dev \
    --no-install-recommends && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Setup PHP-FPM configuration
RUN mkdir -p /run/php

# Copy configuration files
COPY php-docker.conf /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf
COPY php.ini /etc/php/${PHP_VERSION}/fpm/php.ini

# Set up nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# New step:
# Copy base SAE + Module files
#COPY ../../siberian /var/www/html

# Expose ports for nginx
EXPOSE 8080

# Configure supervisord to manage nginx and php-fpm
RUN apt-get update && apt-get install --no-install-recommends -y supervisor && apt-get clean && rm -rf /var/lib/apt/lists/*
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set workdir to shared directory
WORKDIR /var/www/html

# Start supervisord to manage both nginx and php-fpm
CMD ["/usr/bin/supervisord"]
