FROM php:8.4-fpm

RUN apt update && apt install -y \
    	git \
    	unzip \
    	curl \
     	nano \
    	libicu-dev \
		libzip-dev \
    	libpq-dev; \
	docker-php-ext-install intl opcache zip pdo pdo_pgsql; \
	pecl install apcu; \
	docker-php-ext-enable apcu

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/app

COPY docker/php/conf.d/*.ini /usr/local/etc/php/conf.d/
