# Stage 1: Composer build
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# Install dependencies without dev packages and scripts
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
# Run scripts after copying code
RUN composer dump-autoload --optimize --no-dev

# Stage 2: Node build
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 3: Production Image
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    icu-dev \
    oniguruma-dev \
    bash \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath intl \
    && apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del pcre-dev $PHPIZE_DEPS

# Copy application files
COPY . .

# Copy vendor from composer stage
COPY --from=vendor /app/vendor/ /var/www/html/vendor/

# Copy built assets from frontend stage
COPY --from=frontend /app/public/build/ /var/www/html/public/build/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Switch to non-root user
USER www-data

EXPOSE 9000

CMD ["php-fpm"]
