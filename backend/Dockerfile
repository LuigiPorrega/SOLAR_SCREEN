FROM php:8.1-apache

# Instalar dependencias
RUN apt-get update && apt-get upgrade -y && apt-get dist-upgrade -y
RUN apt-get install -y \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    libzip-dev libicu-dev git unzip curl \
    && rm -rf /var/lib/apt/lists/*

# Extensiones PHP
RUN docker-php-ext-install mysqli pdo_mysql gd zip intl

# Habilitar módulos Apache
RUN a2enmod rewrite headers

# Copiar configuración de Apache
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Pasar cabecera Authorization
RUN echo "SetEnvIf Authorization \"(.*)\" HTTP_AUTHORIZATION=$1" >> /etc/apache2/apache2.conf

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar aplicación
COPY . /var/www/html

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Permisos correctos para Apache
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 /var/www/html/writable

# Exponer puerto
EXPOSE 80

# Ejecutar Apache como www-data (usuario por defecto)
USER www-data
