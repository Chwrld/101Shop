# Use official PHP + Apache image (patched PHP2Shell branch)
FROM php:8.4-apache

# Fail the build on an unpatched PHP2Shell runtime
RUN php -r 'exit(version_compare(PHP_VERSION, "8.4.27", "<") ? 1 : 0);' \
    || (echo "PHP >= 8.4.27 required (PHP2Shell CVE-2026-109618/109619)" && exit 1)

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
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli mbstring exif pcntl bcmath gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project files and install PHP dependencies
# (before hardening ini: Composer may need functions we disable at runtime)
COPY . /var/www/html/
RUN composer install --no-dev --optimize-autoloader --no-interaction --working-dir=/var/www/html

# PHP2Shell / general hardening (see docs/fix/php2shell-and-bugfix-plan.md §6)
COPY docker/php-hardening.ini /usr/local/etc/php/conf.d/99-hardening.ini
COPY docker/apache-hardening.conf /etc/apache2/conf-available/hardening.conf
# Only public/ is served; app/, database/ and the repo root stay outside the docroot
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2enconf hardening \
    && mkdir -p /var/lib/php/sessions /var/www/html/storage/logs \
    && chown www-data:www-data /var/lib/php/sessions /var/www/html/storage/logs

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose Apache port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
