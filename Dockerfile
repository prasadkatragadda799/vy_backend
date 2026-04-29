# AWS-friendly Apache + PHP image
FROM php:8.2-apache-bookworm

# Install required extensions and utilities
RUN apt-get update \
    && apt-get install -y --no-install-recommends default-libmysqlclient-dev curl \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html

# Serve from /public and keep uploads writable for runtime user.
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && mkdir -p /var/www/html/storage/uploads/donations /var/www/html/storage/uploads/registrations \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
  CMD curl -fsS http://localhost/api/health || exit 1
