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

# Run migrations if database is ready (for php-fpm or supervisord)
if [ "$1" = "php-fpm" ] || [ "$1" = "/usr/bin/supervisord" ]; then
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

# Substitute PORT in nginx config if needed (Railway provides $PORT)
if [ -n "$PORT" ]; then
    echo "Substituting PORT=$PORT into nginx config"
    sed -i "s/listen \$PORT;/listen $PORT;/" /etc/nginx/sites-available/default
fi

exec "$@"
