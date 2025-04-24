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
    libfreetype6-dev && \
    docker-php-ext-install pdo pdo_pgsql && \
    rm -rf /var/lib/apt/lists/*



RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/symfony

COPY . /var/www/symfony

RUN composer install --ignore-platform-reqs
