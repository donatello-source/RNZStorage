FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    zip \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    librabbitmq-dev \
    libssl-dev \
    libzip-dev && \
    docker-php-ext-install pdo pdo_pgsql gd zip && \
    pecl install -f amqp || true && docker-php-ext-enable amqp && \
    rm -rf /var/lib/apt/lists/*


RUN docker-php-ext-install opcache

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/symfony

COPY . /var/www/symfony

RUN composer install --ignore-platform-reqs