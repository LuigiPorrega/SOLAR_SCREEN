FROM php:8.1-apache

# Install dependencies and upgrade to the latest versions
RUN apt-get update && apt-get upgrade -y && apt-get dist-upgrade -y

# Install system dependencies and remove unused cache
RUN apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    git \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo_mysql gd zip intl

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Habilitar mod_headers
RUN a2enmod headers

# Copy the custom Apache configuration file to the container
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Add a rule to make Apache pass the Authorization header correctly
RUN echo "SetEnvIf Authorization \"(.*)\" HTTP_AUTHORIZATION=$1" >> /etc/apache2/apache2.conf

# Set the working directory
WORKDIR /var/www/html

# Copy application files into the container
COPY . /var/www/html

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Run composer install to install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Change file permissions to allow Apache to access the files
RUN chown -R www-data:www-data /var/www/html

# Expose the port the app is running on
EXPOSE 80

# Set a non-root user for better security (optional)
RUN useradd -ms /bin/bash myuser
USER myuser
