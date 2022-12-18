FROM php:8.1-fpm

RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

RUN apt-get update \
  && apt-get install -y libxml2-dev \
  && apt-get -y autoremove \
  && apt-get clean

RUN docker-php-ext-install soap

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install bcmath