# Multi-stage build for LytePHP
FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
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

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application code
COPY . .

# Create public directory if it doesn't exist
RUN mkdir -p public

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Development stage
FROM base AS development

# Install development dependencies
RUN composer install --optimize-autoloader --no-interaction

# Enable error reporting for development
RUN echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Expose port
EXPOSE 8000

# Start development server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]

# Production stage
FROM base AS production

# Copy only production files
COPY --from=base /var/www/html /var/www/html

# Remove development files
RUN rm -rf tests/ \
    && rm -rf .git/ \
    && rm -rf .idea/ \
    && rm -rf docs/ \
    && rm -rf examples/

# Optimize for production
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Expose port
EXPOSE 8000

# Start production server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"] 