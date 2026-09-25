#!/bin/bash
set -e

# Generate APP_KEY if not set
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Support Railway MySQL addon environment variables
# Railway provides: MYSQLHOST, MYSQLPORT, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE
if [ -n "$MYSQLHOST" ]; then
    export DB_HOST=${MYSQLHOST}
    export DB_PORT=${MYSQLPORT:-3306}
    export DB_USERNAME=${MYSQLUSER}
    export DB_PASSWORD=${MYSQLPASSWORD}
    export DB_DATABASE=${MYSQLDATABASE}
fi

# Run migrations if database is ready (only when running php-fpm)
if [ "$1" = "php-fpm" ]; then
    # Wait for MySQL to be ready
    echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
    until MYSQL_PWD=${DB_PASSWORD} mysql -h ${DB_HOST} -u ${DB_USERNAME} -P ${DB_PORT} -e "SELECT 1" &> /dev/null; do
        echo "Waiting for MySQL..."
        sleep 2
    done
    
    echo "Database is ready. Running migrations..."
    php artisan migrate --force
    php artisan db:seed --force
fi

exec "$@"
