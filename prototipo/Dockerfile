
FROM php:8.2-apache

# Instala as extensões necessárias para conexão com MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Habilita o módulo de reescrita do Apache
RUN a2enmod rewrite

# Define a pasta da aplicação
WORKDIR /var/www/html
