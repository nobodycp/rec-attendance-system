#!/bin/bash
set -e

cd /var/www/html

uses_mysql() {
    [ -n "${DATABASE_URL:-}" ] \
        || [ -n "${MYSQL_URL:-}" ] \
        || [ -n "${DB_URL:-}" ] \
        || [ "${DB_DRIVER:-mysql}" = "mysql" ]
}

if uses_mysql; then
    echo "Waiting for MySQL (max 60 seconds)..."
    attempts=0
    max_attempts=30

    until php docker/wait-db.php; do
        attempts=$((attempts + 1))
        if [ "$attempts" -ge "$max_attempts" ]; then
            echo "WARNING: MySQL not ready after ${max_attempts} attempts."
            echo "Starting web server anyway — check /debug/db with APP_DEBUG=true"
            break
        fi
        sleep 2
    done

    if php docker/wait-db.php 2>/dev/null; then
        echo "Initializing database schema..."
        php docker/init-db.php || echo "WARNING: schema init failed — see container logs."
    fi
fi

echo "Starting application..."
exec "$@"
