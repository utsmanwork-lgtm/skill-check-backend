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

# Wait a bit for the database to start, then try to run migrations (non-blocking)
if [ -n "$DB_HOST" ]; then
    echo "Waiting for MySQL to start... (will try to run migrations after a short delay)"
    sleep 5
    echo "Attempting to run migrations..."
    if php artisan migrate --force --no-interaction; then
        echo "Migrations ran successfully."
        php artisan db:seed --force
    else
        echo "WARNING: Migrations failed. The application will still start, but you may need to run migrations manually."
    fi
fi

# If the command is "serve", run the Laravel development server
if [ "$1" = "serve" ]; then
    echo "Starting Laravel development server on host 0.0.0.0 port ${PORT:-8000}"
    exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
fi

# Default: exec the provided command (e.g., for bash, etc.)
exec "$@"