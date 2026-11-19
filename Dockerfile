FROM php:8.4-apache
WORKDIR /var/www/html
RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

RUN sed -ri \
    's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf

EXPOSE 80