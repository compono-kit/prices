FROM php:8.5-alpine

ENV XDEBUG_VERSION=3.5.3

RUN apk add --update --no-cache linux-headers $PHPIZE_DEPS \
	&& pecl install xdebug-${XDEBUG_VERSION} \
	&& docker-php-ext-enable xdebug \
	&& apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENTRYPOINT ["composer"]
