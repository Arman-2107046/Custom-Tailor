# Stage 1: Build frontend assets
FROM node:20 AS frontend-builder

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# Stage 2: PHP + Nginx
FROM php:8.4-fpm

# Install Nginx and system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files and install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --optimize-autoloader --no-dev

# Copy application code
COPY . .

# Copy built frontend assets from builder
COPY --from=frontend-builder /app/public/build /var/www/public/build

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# Configure Nginx
RUN echo 'server { \
    listen 80; \
    server_name _; \
    root /var/www/public; \
    add_header X-Frame-Options "SAMEORIGIN"; \
    add_header X-Content-Type-Options "nosniff"; \
    index index.php; \
    charset utf-8; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location = /favicon.ico { access_log off; log_not_found off; } \
    location = /robots.txt { access_log off; log_not_found off; } \
    error_page 404 /index.php; \
    location ~ \.php$ { \
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock; \
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name; \
        include fastcgi_params; \
    } \
    location ~ /\.(?!well-known).* { \
        deny all; \
    } \
}' > /etc/nginx/sites-available/default

# Configure Supervisor
RUN echo '[supervisord] \n\
nodaemon=true \n\
user=root \n\
logfile=/var/log/supervisor/supervisord.log \n\
pidfile=/var/run/supervisord.pid \n\
\n\
[program:php-fpm] \n\
command=/usr/local/sbin/php-fpm \n\
autostart=true \n\
autorestart=true \n\
stdout_logfile=/var/log/supervisor/php-fpm.log \n\
stderr_logfile=/var/log/supervisor/php-fpm-error.log \n\
\n\
[program:nginx] \n\
command=/usr/sbin/nginx -g "daemon off;" \n\
autostart=true \n\
autorestart=true \n\
stdout_logfile=/var/log/supervisor/nginx.log \n\
stderr_logfile=/var/log/supervisor/nginx-error.log' > /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache && \
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/public/build

# Expose port 80 for Nginx
EXPOSE 80

# Start supervisor (which runs both PHP-FPM and Nginx)
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
