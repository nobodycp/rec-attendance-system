#!/bin/bash
set -e

cd /var/www/html

if [ "${DB_DRIVER:-mysql}" = "mysql" ]; then
    echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
    until php docker/wait-db.php; do
        echo "MySQL not ready — retrying in 2s..."
        sleep 2
    done

    echo "Initializing database schema..."
    php docker/init-db.php
fi

echo "Starting application..."
exec "$@"
