FROM php:8.1-apache

# Instalar dependencias del sistema operativo
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libicu-dev

# Instalar y habilitar extensiones de PHP
RUN docker-php-ext-install mysqli pdo_mysql gd zip intl

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Copiar la configuración de Apache
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar los archivos de la aplicación
COPY . /var/www/html

# Cambiar los permisos
RUN chown -R www-data:www-data /var/www/html
