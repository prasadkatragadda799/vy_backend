# Render-ready PHP API (PHP built-in server on PORT)
FROM php:8.2-slim

# PDO MySQL for external DB; SQLite is included in PHP
RUN apt-get update && apt-get install -y --no-install-recommends default-libmysqlclient-dev \
    && docker-php-ext-install pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . /app

# Ensure storage exists and is writable (ephemeral on Render; use external storage in production)
RUN mkdir -p storage/uploads/donations storage/uploads/registrations \
    && chmod -R 775 storage

# Render sets PORT (e.g. 10000). Bind to 0.0.0.0 to accept external requests.
ENV PORT=8080
EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public"]
