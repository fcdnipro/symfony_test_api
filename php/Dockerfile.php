FROM php:8.1-fpm

# Set environment variable to allow Composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip libpq-dev

# Install PHP extensions
RUN docker-php-ext-install intl opcache pdo_pgsql zip

# Install APCu
RUN pecl install apcu \
&& docker-php-ext-enable apcu

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Symfony CLI globally
RUN curl -sS https://get.symfony.com/cli/installer | bash \
&& mv /root/.symfony5 /root/.symfony \
&& ln -s /root/.symfony/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/project

# Clean up
RUN apt-get clean && rm -rf /var/lib/apt/lists/*
