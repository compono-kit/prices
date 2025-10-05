FROM php:8.4-fpm-alpine
ENV XDEBUG_VERSION 3.4.0

RUN apk add --update --no-cache linux-headers nano icu-dev $PHPIZE_DEPS
RUN pecl install xdebug-${XDEBUG_VERSION}

RUN docker-php-ext-install bcmath intl pdo_mysql
RUN docker-php-ext-enable xdebug

CMD ["php-fpm"]
