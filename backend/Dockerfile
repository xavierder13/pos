FROM php:8.4-fpm

# =========================================
# SYSTEM DEPENDENCIES + PHP EXTENSIONS
# =========================================
RUN apt-get update && apt-get install -y \
    git curl unzip zip \
    libpng-dev libonig-dev libxml2-dev libzip-dev libicu-dev \
    && docker-php-ext-install \
    pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# =========================================
# COMPOSER
# =========================================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_HOME=/tmp/composer

# =========================================
# RUN AS THE HOST USER (bind-mounted files stay owned by you)
# =========================================
ARG UID=1000
ARG GID=1000
RUN groupmod -o -g ${GID} www-data && usermod -o -u ${UID} -g www-data www-data

WORKDIR /var/www/html

# =========================================
# COPY & INSTALL DEPENDENCIES
# =========================================
# docker-compose.prod.yml passes --no-dev
ARG COMPOSER_FLAGS=""
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --no-interaction --prefer-dist ${COMPOSER_FLAGS}
COPY --chown=www-data:www-data . .
RUN composer dump-autoload --optimize ${COMPOSER_FLAGS} \
    && chown -R www-data:www-data /var/www/html/vendor /var/www/html/bootstrap/cache /var/www/html/storage

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
