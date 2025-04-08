FROM php:8.1-apache

# Instalar dependencias del sistema operativo
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    git \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

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

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Ejecutar composer install para instalar las dependencias de PHP automáticamente, incluyendo TCPDF
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Cambiar los permisos para que Apache tenga acceso a los archivos
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto 80
EXPOSE 80
