FROM php:8.3-fpm

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV PATH="$PATH:/usr/local/bin"

# Install required libs
RUN apt update
RUN apt install libldap-dev libgmp-dev git unzip socat -y

# Install LDAP and GMP extensions
RUN docker-php-ext-install ldap gmp sqlite3

# Pull in project source
COPY --chown=www-data:www-data server /var/www/project
RUN chown www-data:www-data /var/www

# Pull in secret key
COPY --chown=www-data:www-data secrets/var/oauth /var/oauth
RUN chown www-data:www-data /var/oauth

# Switch to WWW user
WORKDIR /var/www/project
USER www-data

# Pull vendor files
ENV SYMFONY_ENV=prod
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV DATABASE_DSN="sqlite:///%kernel.project_dir%/var/app.db"
RUN composer install --no-dev --optimize-autoloader
RUN php bin/console importmap:install

# Package up assets
RUN php bin/console sass:build
RUN php bin/console asset-map:compile
RUN php bin/console assets:install

# Clear any cache
RUN php bin/console cache:pool:clear --all

# Health check
HEALTHCHECK CMD socat -u OPEN:/dev/null UNIX-CONNECT:/run/php8.3-fpm.sock;

# Run app as root
USER root

# Start shim