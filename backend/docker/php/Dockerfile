FROM php:8.0-fpm

RUN docker-php-ext-install mysqli
RUN apt update && apt install libgmp-dev -y && docker-php-ext-install gmp
RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis