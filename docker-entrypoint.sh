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
    echo "MySQL config from Railway env vars:"
    echo "  DB_HOST=$DB_HOST"
    echo "  DB_PORT=$DB_PORT"
    echo "  DB_USERNAME=$DB_USERNAME"
    echo "  DB_DATABASE=${DB_DATABASE}"
    # Do not print password
else
    echo "WARNING: MYSQLHOST not set, using existing DB_* vars"
fi

# Wait for MySQL if DB vars are set (for serve, migrate, etc.)
if [ -n "$DB_HOST" ]; then
    echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
    timeout=30
    ready=false
    while [ $timeout -gt 0 ]; do
        if MYSQL_PWD=${DB_PASSWORD} mysql -h ${DB_HOST} -u ${DB_USERNAME} -P ${DB_PORT} -e "SELECT 1" &> /dev/null; then
            ready=true
            break
        fi
        echo "Waiting for MySQL... ($timeout seconds left)"
        sleep 2
        timeout=$((timeout - 2))
    done
    
    if [ "$ready" = true ]; then
        echo "Database is ready. Running migrations..."
        php artisan migrate --force
        php artisan db:seed --force
    else
        echo "WARNING: Could not connect to MySQL after 30 seconds. Continuing without migrations."
        echo "Migrations will need to run manually or on next deployment."
    fi
fi

# If the command is "serve", run the Laravel development server
if [ "$1" = "serve" ]; then
    echo "Starting Laravel development server on host 0.0.0.0 port ${PORT:-8000}"
    exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
fi

# Default: exec the provided command (e.g., for bash, etc.)
exec "$@"
