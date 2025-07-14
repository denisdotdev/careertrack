# Multi-stage build for Laravel application
FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite \
    sqlite-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo \
    pdo_sqlite \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    xml

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application code first
COPY . .

# Create cache directory and set permissions
RUN mkdir -p bootstrap/cache \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && chmod -R 755 bootstrap/cache \
    && chmod -R 755 storage \
    && chown -R www-data:www-data bootstrap/cache \
    && chown -R www-data:www-data storage

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create .env file from example if it doesn't exist
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Generate application key
RUN php artisan key:generate --no-interaction

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Frontend build stage
FROM node:18-alpine AS frontend

WORKDIR /var/www/html

# Copy package files
COPY package.json package-lock.json ./

# Install Node.js dependencies (including dev dependencies for build)
RUN npm ci

# Copy frontend source files
COPY resources/ ./resources/
COPY vite.config.js ./

# Build frontend assets
RUN npm run build

# Production stage
FROM php:8.2-fpm-alpine AS production

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    sqlite \
    sqlite-dev \
    libpng-dev \
    libxml2-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    openssl

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo \
    pdo_sqlite \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    xml

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application from base stage
COPY --from=base /var/www/html .

# Copy built frontend assets
COPY --from=frontend /var/www/html/public/build ./public/build

# Create .env file from example if it doesn't exist
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Generate application key if not exists
RUN if [ -z "$(grep '^APP_KEY=' .env | cut -d '=' -f2)" ]; then php artisan key:generate --no-interaction; fi

# Ensure view directory exists
RUN mkdir -p resources/views

# Optimize Laravel for production
RUN php artisan config:cache \
    && php artisan route:cache \
    && (php artisan view:cache || true)

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && chmod -R 777 /var/www/html/storage/framework/cache \
    && chmod -R 777 /var/www/html/storage/framework/sessions \
    && chmod -R 777 /var/www/html/storage/framework/views \
    && chmod -R 777 /var/www/html/storage/logs

# Copy Nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default-ssl.conf /etc/nginx/conf.d/default.conf
COPY docker/ssl.conf /etc/nginx/ssl.conf

# Copy and generate SSL certificates (production stage only)
COPY docker/generate-ssl.sh /generate-ssl.sh
RUN chmod +x /generate-ssl.sh && /generate-ssl.sh

# Copy startup script
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Expose port
EXPOSE 80

# Start services
CMD ["/start.sh"] 