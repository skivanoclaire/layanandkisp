# Gunakan image PHP 8.4 + Apache
FROM php:8.4-apache

# 1) System deps (termasuk libmagickwand-dev untuk imagick)
RUN apt-get update && apt-get install -y --no-install-recommends \
    git zip unzip curl \
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libxml2-dev libonig-dev \
    libmagickwand-dev \
 && rm -rf /var/lib/apt/lists/*

# 2) Ekstensi PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip mbstring xml

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

# 3) Imagick via PECL
RUN pecl install imagick \
 && docker-php-ext-enable imagick

# 4) Apache rewrite + DocumentRoot ke /public + Timeout settings
RUN a2enmod rewrite
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Apache timeout configuration
RUN printf "\
Timeout 3600\n\
ProxyTimeout 3600\n\
" >> /etc/apache2/apache2.conf

# Virtual host configuration untuk handling large requests
RUN printf "\
<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    \n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
        \n\
        # Allow large POST data\n\
        LimitRequestBody 268435456\n\
    </Directory>\n\
    \n\
    # Timeout settings\n\
    Timeout 3600\n\
    \n\
    ErrorLog \${APACHE_LOG_DIR}/error.log\n\
    CustomLog \${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>\n\
" > /etc/apache2/sites-available/000-default.conf

# 5) Konfigurasi PHP untuk handling data besar dan Google Sheets
RUN printf "\
upload_max_filesize=256M\n\
post_max_size=256M\n\
max_execution_time=3600\n\
max_input_time=3600\n\
memory_limit=512M\n\
max_input_vars=10000\n\
default_socket_timeout=3600\n\
" > /usr/local/etc/php/conf.d/custom.ini


# 6) Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 7) Copy source
COPY ./app /var/www/html

# 8) Install dependencies aplikasi
WORKDIR /var/www/html
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Install dependencies dan build assets
WORKDIR /var/www/html
COPY package*.json ./
RUN npm install
COPY . .

# 9) Permission & bootstrap
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 storage bootstrap/cache

# 10) Pakai user non-root saat runtime
USER www-data

