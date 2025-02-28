#!/bin/bash
set -e

DIR_CONFIG="/etc/uwu"
DIR_SERVER="/var/www/uwu"

BIN_CONSOLE="${DIR_SERVER}/bin/console"
BIN_PHPUNIT="${DIR_SERVER}/vendor/bin/phpunit"
BIN_PHP="/usr/local/bin/php"
BIN_PHP_FPM="/usr/local/sbin/php-fpm"
BIN_OPENSSL="/usr/bin/openssl"
BIN_SQLITE="/usr/bin/sqlite3"

CONF_PRIVATE_KEY="${DIR_CONFIG}/private.key"
CONF_PUBLIC_KEY="${DIR_CONFIG}/public.key"
CONF_OAUTH_DB="${DIR_CONFIG}/oauth.db"
CONF_FAVICON="${DIR_CONFIG}/favicon.svg"

PROD_FAVICON="${DIR_SERVER}/public/img/favicon_custom.svg"

# Check for DB existence
if [ ! -f "$CONF_OAUTH_DB" ]; then
  echo "Database not found, creating...";
  sqlite3 $CONF_OAUTH_DB "VACUUM";

  # Await filesystem update
  for i in $(seq 16);
    do
      if [ -f "$CONF_OAUTH_DB" ]; then
        break;
      elif [ "$i" -eq "16" ]; then
        echo "Database wasn't created. Bailing.";
        exit 1;
      else
        sleep 0.25;
      fi
    done

  chown www-data:www-data $CONF_OAUTH_DB;
  php $BIN_CONSOLE doctrine:database:create;
  php $BIN_CONSOLE doctrine:schema:update --dump-sql --force
fi

# Check for pubkey/privkey existence
if [ ! -f "$CONF_PRIVATE_KEY" ]; then
  echo "Keys not found, creating...";
  $BIN_OPENSSL genrsa -out $CONF_PRIVATE_KEY 2048;
  $BIN_OPENSSL rsa -in $CONF_PRIVATE_KEY -pubout -out $CONF_PUBLIC_KEY;
fi

# Assert ownership over config root
chown -R www-data:www-data $DIR_CONFIG

# Start server
service nginx start
environment=${APP_ENV,,:-prod}
if [ "${environment}" = "test" ]; then
  echo "Starting in test mode...";
  cd $DIR_SERVER;
  APP_ENV=test APP_DEBUG=1 $BIN_PHP $BIN_PHPUNIT;
elif [ "${environment}" = "dev" ]; then
  echo "Starting in development mode...";
  APP_ENV=dev APP_DEBUG=1 $BIN_PHP_FPM;
else
  echo "Starting in production mode...";
  APP_ENV=prod APP_DEBUG=0 $BIN_PHP_FPM;
fi
