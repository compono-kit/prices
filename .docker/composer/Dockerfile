FROM php:8.4-fpm-alpine
ARG GITHUB_TOKEN

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer config --global github-oauth.github.com ${GITHUB_TOKEN}
RUN composer self-update

RUN apk add --update --no-cache linux-headers nano icu-dev $PHPIZE_DEPS
RUN docker-php-ext-install bcmath intl

ENTRYPOINT ["composer"]
