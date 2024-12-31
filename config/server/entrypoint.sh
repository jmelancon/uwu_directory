#!/bin/sh
set -e

# Check for DB existence
if [ ! -f /var/server/app.db ]; then
  echo "Database not found, creating...";
  sqlite3 /var/server/app.db "VACUUM;";
  sleep 2;
  chown www-data:www-data /var/server/app.db;
  php /var/www/project/bin/console doctrine:database:create;
  php /var/www/project/bin/console doctrine:schema:update --dump-sql --force
fi

# Check for pubkey/privkey existence
if [ ! -f /var/server/private.key ]; then
  echo "Keys not found, creating...";
  openssl genrsa -out /var/server/private.key 2048;
  openssl rsa -in /var/server/private.key -pubout -out /var/server/public.key;
fi

# Start server
php-fpm;
