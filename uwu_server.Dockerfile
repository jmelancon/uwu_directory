FROM php:8.3-fpm AS uwu_thin

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV PATH="$PATH:/usr/local/bin"

# Install required libs
RUN apt update
RUN apt install libldap-dev libgmp-dev git unzip socat sqlite3 npm -y

# Install LDAP and GMP extensions
RUN docker-php-ext-install ldap gmp

# Configure PHP-FPM
COPY ./config/server/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# Ensure config path
RUN mkdir /var/uwu
RUN chown www-data:www-data /var/uwu

# Health check
HEALTHCHECK CMD socat -u OPEN:/dev/null UNIX-CONNECT:/run/php8.3-fpm.sock;

# Provision shim
COPY ./config/server/entrypoint.sh /entrypoint.sh
RUN chmod 755 /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

#
# For production and test builds, we'll need to pull in server files into
# the container. This is skipped for devel builds as I just do bind mounts for those.
#
FROM uwu_thin AS uwu_full
# Pull in project source
COPY --chown=www-data:www-data server /var/www/uwu
RUN chown www-data:www-data /var/www

# Switch to WWW user
WORKDIR /var/www/uwu
USER www-data

# Install all NPM packages
RUN npm install

USER root

#
# Production images require provisioning. Don't pull in dev dependencies.
#
FROM uwu_full AS prod
USER www-data
ENV SYMFONY_ENV=prod
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV DATABASE_DSN="sqlite:///var/uwu/app.db"
RUN touch /var/www/uwu/.env

# Pull vendor files & compile
RUN composer install --no-dev --optimize-autoloader

USER root

#
# Test images also require provisioning. Pull in dev dependencies as we'll need phpunit.
#
FROM uwu_full AS test
USER www-data
ENV SYMFONY_ENV=test
ENV APP_ENV=test
ENV APP_DEBUG=1
ENV DATABASE_DSN="sqlite:///var/uwu/app.db"
RUN touch /var/www/uwu/.env

# Pull vendor files & compile
RUN composer install --dev --optimize-autoloader

USER root

#
# I just kinda yolo stuff for dev builds
#
FROM uwu_thin AS devel
RUN echo "haiiii!!!!"