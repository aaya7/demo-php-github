FROM php:8.2-apache

# Install SQLite extensions
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    sqlite3 \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_sqlite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for layer caching
COPY composer.json phpunit.xml ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader || true

# Copy application files
COPY src/ /var/www/html/

# Create data directory for SQLite
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html/data

# Apache listens on 80 internally
EXPOSE 80