FROM php:8.2-apache

# Establecer directorio de trabajo
WORKDIR /var/www/

# Instalar dependencias y extensiones PHP necesarias
RUN apt-get update && apt-get install --yes --no-install-recommends \
        zlib1g-dev \
        libzip-dev \
        unzip \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libssl-dev \
        curl \
    && docker-php-ext-install zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql \
    \
    # Instalar MongoDB driver
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && echo "extension=mongodb.so" >> /usr/local/etc/php/php.ini \
    \
    # Instalar Xdebug
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Copiar configuración de Xdebug
COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Instalar Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
    && composer require mongodb/mongodb

# Etiqueta descriptiva
LABEL description="PHP 8.2 + Apache + GD + PDO + MongoDB + Xdebug + Composer"
