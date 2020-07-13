FROM php:7.3-apache

RUN a2enmod rewrite expires

RUN apt-get update && apt-get install -y \
    libmemcached-dev \
    zip \
    zlib1g \
    zlib1g-dev \
    unzip \
    curl \
    build-essential \
    --no-install-recommends \
    && rm -r /var/lib/apt/lists/*

# Install Memcached for php 7
RUN curl -L -o /tmp/memcached.tar.gz "https://github.com/php-memcached-dev/php-memcached/archive/v3.1.5.tar.gz" \
    && mkdir -p /usr/src/php/ext/memcached \
    && tar -C /usr/src/php/ext/memcached -zxvf /tmp/memcached.tar.gz --strip 1 \
    && docker-php-ext-configure memcached \
    && docker-php-ext-install memcached \
    && rm /tmp/memcached.tar.gz

RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# Composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

ADD ./development/docker/localhost /etc/apache2/sites-available/000-default.conf
ADD ./composer.json /var/www/composer.json

WORKDIR /var/www/development

RUN cd /var/www/development
RUN mkdir cache
RUN chmod 777 cache

RUN cd /var/www/
RUN composer install

EXPOSE 80



