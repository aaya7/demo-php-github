# ============================================================
# Stage 1: test — runs PHPUnit, includes dev dependencies
# ============================================================
FROM php:8.2-cli AS test

RUN apt-get update && apt-get install -y \
        libsqlite3-dev \
        sqlite3 \
        unzip \
        git \
    && docker-php-ext-install pdo pdo_sqlite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json phpunit.xml ./
RUN composer install --no-interaction --prefer-dist

COPY src/ ./src/
COPY tests/ ./tests/

CMD ["vendor/bin/phpunit", "--testdox"]

# ============================================================
# Stage 2: runtime — Apache + PHP, no test files
# ============================================================
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
        libsqlite3-dev \
        sqlite3 \
        unzip \
        git \
    && docker-php-ext-install pdo pdo_sqlite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY composer.json ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader || true

COPY src/ /var/www/html/
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html/data

EXPOSE 80