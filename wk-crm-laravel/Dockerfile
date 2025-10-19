FROM php:8.2-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    autoconf \
    gcc \
    g++ \
    make

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    gd \
    xml \
    bcmath \
    pcntl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application code first
COPY . .

# Create Laravel directories and set permissions
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
RUN chmod -R 777 storage bootstrap/cache

# Install PHP dependencies (after copying artisan)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Generate app key if needed
RUN php artisan key:generate --show

# Expose port
EXPOSE 8000

# Start Laravel development server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]