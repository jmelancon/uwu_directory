FROM php:8.3-fpm

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV PATH="$PATH:/usr/local/bin"

# Install required libs
RUN apt update
RUN apt install libldap-dev libgmp-dev git unzip socat sqlite3 npm -y

# Install LDAP and GMP extensions
RUN docker-php-ext-install ldap gmp

# Configure PHP-FPM
COPY ../../config/server/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# Switch to WWW user
WORKDIR /var/www/uwu
USER www-data

# Ensure a few environment variables
ENV SYMFONY_ENV=dev
ENV APP_ENV=dev
ENV APP_DEBUG=1
ENV DATABASE_DSN="sqlite:///var/uwu/oauth.db"

# Health check
HEALTHCHECK CMD socat -u OPEN:/dev/null UNIX-CONNECT:/run/php8.3-fpm.sock;

# Run app as root
USER root

# Provision shim
COPY ../../config/server/entrypoint.sh /entrypoint.sh
RUN chmod 755 /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]