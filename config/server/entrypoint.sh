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

# Ensure custom.scss exists
if [ ! -f /var/server/custom.scss ]; then
  echo "custom.scss not found, creating...";
  cp /var/www/project/assets/reference/custom.scss /var/server/custom.scss/;
fi

# Do we have a favicon to copy?
if [ ! -f /var/server/favicon.svg ]; then
  rm -f /var/www/project/public/img/favicon_custom.svg;
elif [ ! -f /var/www/project/public/img/favicon_custom.svg ]; then
  cp /var/server/favicon.svg /var/www/project/public/img/favicon_custom.svg;
  chown www-data /var/www/project/public/img/favicon_custom.svg;
fi;

# Compile stylesheets & js
/usr/bin/npm --prefix /var/www/project run build;

# Start server
php-fpm;
