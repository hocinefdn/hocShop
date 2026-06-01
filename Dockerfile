FROM php:8.4-fpm-alpine

# Installer les extensions PDO nécessaires pour MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Installer Composer globalement
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm"]