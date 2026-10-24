# Siberian CMS images. Build context is the repository root:
#
#   docker build -f docker/Dockerfile --target app      --build-arg PHP_VERSION=8.5 -t siberian-app .
#   docker build -f docker/Dockerfile --target web                                  -t siberian-web .
#   docker build -f docker/Dockerfile --target allinone --build-arg PHP_VERSION=8.5 -t siberian .
#
# app      php-fpm, also runs the cron loop or any CLI command (see docker/entrypoint/app.sh)
# web      nginx with the Siberian vhost; serves the docroot volume, php-fpm is reached through SIBERIAN_FPM (default app:9000)
# allinone app + nginx + cron in one container, for a demo or a plain `docker run`; the database stays external
#
# The database is never baked in: use the stock mariadb image (see docker/docker-compose.yml).
# The PHP extension list is the one of tests/php-upgrade/Dockerfile.ci, which gates every PHP version.
ARG PHP_VERSION=8.5

# ---------------------------------------------------------------- code
# The docroot as committed (lib/vendor and the committed node_modules included: there is no build step).
FROM scratch AS code
COPY siberian/ /siberian/

# ---------------------------------------------------------------- php runtime
FROM php:${PHP_VERSION}-fpm-bookworm AS php-base

# `apt-get upgrade` on top of a freshly pulled base = latest Debian security fixes at build time
RUN apt-get update && apt-get -y upgrade && apt-get install -y --no-install-recommends \
      g++ gettext-base libicu-dev libxml2-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev libwebp-dev \
      libbz2-dev libtidy-dev libcurl4-openssl-dev libxslt1-dev libzip-dev libsqlite3-dev libonig-dev \
      unzip zip git curl optipng jpegoptim pngquant imagemagick \
    && rm -rf /var/lib/apt/lists/*

# gettext = the PHP extension (needs libc's gettext); opcache is built into PHP 8.5 and has to be added before
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" bcmath bz2 calendar dba exif gd gettext intl mysqli pdo_mysql soap sockets tidy xsl zip \
 && { php -r 'exit(extension_loaded("Zend OPcache") ? 0 : 1);' || docker-php-ext-install opcache; }

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-siberian.ini
COPY docker/php/fpm-pool.conf /usr/local/etc/php-fpm.d/zz-siberian.conf

# ---------------------------------------------------------------- app
FROM php-base AS app
ARG IMAGE_REVISION=unknown
LABEL org.opencontainers.image.title="siberian-app" \
      org.opencontainers.image.source="https://github.com/Xtraball/Siberian" \
      org.opencontainers.image.revision="${IMAGE_REVISION}"

COPY --from=code /siberian /usr/src/siberian
RUN echo "${IMAGE_REVISION}" > /usr/src/siberian.revision
COPY docker/entrypoint/app.sh /usr/local/bin/siberian-entrypoint

# The docroot is a volume that is seeded from /usr/src/siberian on first start, because the in-app updater,
# the installer (app/configs/app.ini) and the uploads all write inside it. See docker/README.md.
VOLUME /var/www/html
WORKDIR /var/www/html
EXPOSE 9000
HEALTHCHECK --interval=15s --timeout=3s --start-period=180s --retries=5 \
  CMD bash -c 'exec 3<>/dev/tcp/127.0.0.1/9000' || exit 1
ENTRYPOINT ["siberian-entrypoint"]
CMD ["php-fpm"]

# ---------------------------------------------------------------- web
FROM nginx:stable-bookworm AS web
ARG IMAGE_REVISION=unknown
LABEL org.opencontainers.image.title="siberian-web" \
      org.opencontainers.image.source="https://github.com/Xtraball/Siberian" \
      org.opencontainers.image.revision="${IMAGE_REVISION}"
# No code in this image: it serves the docroot volume that the app seeds and maintains (mount it read-only
# on /var/www/html, as docker-compose.yml does). Copying the code here too would let it race the app's seeding.
COPY docker/nginx/default.conf.template /etc/nginx/templates/default.conf.template
ENV SIBERIAN_FPM=app:9000
EXPOSE 80
HEALTHCHECK --interval=15s --timeout=3s --start-period=10s --retries=5 \
  CMD curl -fs -o /dev/null http://127.0.0.1/ping.txt || exit 1

# ---------------------------------------------------------------- allinone
FROM app AS allinone
RUN apt-get update && apt-get install -y --no-install-recommends nginx \
 && rm -rf /var/lib/apt/lists/* /etc/nginx/sites-enabled/default /etc/nginx/conf.d/* \
 && ln -sf /dev/stdout /var/log/nginx/access.log && ln -sf /dev/stderr /var/log/nginx/error.log
COPY docker/nginx/default.conf.template /etc/nginx/templates/default.conf.template
COPY docker/entrypoint/allinone.sh /usr/local/bin/siberian-allinone
ENV SIBERIAN_FPM=127.0.0.1:9000
EXPOSE 80
HEALTHCHECK --interval=15s --timeout=3s --start-period=180s --retries=5 \
  CMD curl -fs -o /dev/null http://127.0.0.1/ping.txt || exit 1
ENTRYPOINT ["siberian-allinone"]
CMD []
