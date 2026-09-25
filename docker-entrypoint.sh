#!/bin/bash
set -e

# Generate APP_KEY if not set
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Run migrations if database is ready
if [ "$1" = "php-fpm" ]; then
    # Wait for MySQL to be ready
    until MYSQL_PWD=$DB_PASSWORD mysql -h $DB_HOST -u $DB_USERNAME -e "SELECT 1" &> /dev/null; do
        echo "Waiting for MySQL..."
        sleep 2
    done
    
    echo "Database is ready. Running migrations..."
    php artisan migrate --force
    php artisan db:seed --force
fi

exec "$@"
