FROM php:8.2-fpm

# Update and install required dependencies
RUN apt-get update && apt-get install -y \
    cron \
    g++ \
    gettext \
    libicu-dev \
    openssl \
    libc-client-dev \
    libkrb5-dev \
    libxml2-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libtidy-dev \
    libcurl4-openssl-dev \
    libz-dev \
    libxslt-dev \
    libmemcached-dev \
    bzip2 \
    libbz2-dev \
    libonig-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    bcmath \
    bz2 \
    calendar \
    dba \
    exif \
    fileinfo \
    gettext \
    intl \
    pdo_mysql \
    soap \
    tidy \
    mbstring \
    xsl \
    sockets \
    zip

# Configure GD extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd

# Configure IMAP extension
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install imap

# Create a symbolic link for PHP
RUN ln -s /usr/local/bin/php /usr/bin/php

# Set the working directory
WORKDIR /var/www/html

# Expose PHP-FPM port
EXPOSE 9000

# Set the default command
CMD ["php-fpm"]