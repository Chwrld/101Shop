# Use official PHP + Apache image
FROM php:8.2-apache

# Set working directory inside container
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli mbstring exif pcntl bcmath gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project files and install PHP dependencies
COPY . /var/www/html/
RUN composer install --no-dev --optimize-autoloader --no-interaction --working-dir=/var/www/html || true

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose Apache port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
