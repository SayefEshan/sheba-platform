#!/bin/bash

# Ensure .env exists
if [ ! -f .env ]; then
  echo "Copying .env.example to .env"
  cp .env.example .env
fi

# Wait until MySQL is ready
until nc -z -v -w30 $DB_HOST 3306
do
  echo "Waiting for MySQL..."
  sleep 5
done

# Generate application key (only if not already set)
if ! grep -q "APP_KEY=base64" .env; then
  php artisan key:generate
fi

# Run Laravel migrations
php artisan migrate --force --seed

# Start PHP-FPM
exec php-fpm