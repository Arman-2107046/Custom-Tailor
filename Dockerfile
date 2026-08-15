# Use PHP 8.3 FPM as base image
FROM php:8.3-fpm

# Install system dependencies and Node.js
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Laravel
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files and install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --optimize-autoloader --no-dev

# Copy package files and install NPM dependencies
COPY package*.json ./
RUN npm install --no-fund --no-audit

# Copy the rest of the application code
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# Build frontend assets with memory limit
RUN NODE_OPTIONS="--max-old-space-size=2048" npm run build

# Set proper permissions for Laravel directories
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache && \
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
