FROM php:8.0-fpm-alpine
ARG GITHUB_TOKEN
ENV XDEBUG_VERSION 3.4.0

RUN apk add --update --no-cache linux-headers nano icu-dev $PHPIZE_DEPS
RUN pecl install xdebug-${XDEBUG_VERSION}
RUN docker-php-ext-enable xdebug

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer config --global github-oauth.github.com ${GITHUB_TOKEN}
RUN composer self-update

ENTRYPOINT ["composer"]
