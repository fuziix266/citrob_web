# Dockerfile para citrob_back — Deploy en Docploy
FROM php:8.3-apache

LABEL maintainer="CITROB" \
    org.label-schema.name="CITROB Back - Laminas MVC"

# Actualizar paquetes
RUN apt-get update

# Configurar Apache: public/ como document root
RUN a2enmod rewrite headers \
    && sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Habilitar AllowOverride All para .htaccess
RUN printf '<Directory /var/www/html/public>\n\tOptions Indexes FollowSymLinks\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n' \
    >> /etc/apache2/apache2.conf

# PHP Extensions necesarias
RUN apt-get install --yes git zlib1g-dev libzip-dev libicu-dev \
    && docker-php-ext-install zip intl pdo pdo_mysql

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar código
WORKDIR /var/www/html
COPY . .

# Instalar dependencias de producción
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/data

# Variables de entorno por defecto (Coolify las sobreescribe)
ENV DB_CONNECTION=mariadb
ENV DB_HOST=citrob-citrobbd-dsqwav
ENV DB_PORT=3306
ENV DB_DATABASE=citrobbd
ENV DB_USERNAME=user
ENV DB_PASSWORD=admin@123

EXPOSE 80

# Limpiar config cache y arrancar Apache
CMD ["sh", "-c", "rm -f data/cache/module-config-cache.*.php && apache2-foreground"]
