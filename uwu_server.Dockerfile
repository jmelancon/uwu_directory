# syntax=docker/dockerfile:1-labs
FROM php:8.3-fpm AS private_install_pkgs
RUN apt-get update
RUN apt-get install libldap-dev libgmp-dev git unzip socat sqlite3 nginx -y
RUN docker-php-ext-install ldap gmp

FROM private_install_pkgs AS private_uwu_skeleton
RUN <<EOF
mkdir -p /var/run/uwu
chown -R www-data:www-data /var/run/uwu
mkdir -p /etc/uwu
chown -R www-data:www-data /etc/uwu
mkdir -p /var/www/uwu
chown -R www-data:www-data /var/www
EOF

FROM private_uwu_skeleton AS private_buildenv_node
USER www-data
RUN <<EOF
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh | bash;
export NVM_DIR="$HOME/.nvm";
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh";
nvm install node;
nvm install-latest-npm;
EOF
USER root

FROM private_buildenv_node AS private_src_assets
COPY --parents --chown=www-data:www-data  \
    ./assets                    \
    ./public                    \
    package.json                \
    package-lock.json           \
    webpack.config.js           \
    tsconfig.json               \
    /var/www/uwu/

FROM private_src_assets AS private_src_assets_compiled
WORKDIR /var/www/uwu
USER www-data
RUN <<EOF
export NVM_DIR="$HOME/.nvm";
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh";
nvm use node;
npm install;
npm run build;
EOF
USER root

FROM private_uwu_skeleton AS private_buildenv_composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV PATH="$PATH:/usr/local/bin"

FROM private_buildenv_composer AS private_src_php
COPY --parents --chown=www-data:www-data \
    bin                        \
    config                     \
    public                     \
    src                        \
    templates                  \
    composer.json              \
    composer.lock              \
    /var/www/uwu/

FROM private_src_php AS private_src_and_vendor
WORKDIR /var/www/uwu
USER www-data
RUN <<EOF
touch /var/www/uwu/.env;
DATABASE_DSN="sqlite:///etc/uwu/oauth.db" composer install --optimize-autoloader;
EOF
USER root

FROM private_uwu_skeleton AS private_src_tests
COPY --parents --chown=www-data:www-data   \
    tests                        \
    phpunit.xml.dist             \
    /var/www/uwu/

FROM private_uwu_skeleton AS private_all_src
COPY --from=private_src_and_vendor /var/www/uwu /var/www/uwu
COPY --from=private_src_assets_compiled /var/www/uwu/public/build /var/www/uwu/public/build
COPY --from=private_src_tests /var/www/uwu /var/www/uwu

FROM private_all_src AS uwu_server
COPY --chmod=755 sh/server_entrypoint.sh /entrypoint.sh
COPY <<EOF /usr/local/etc/php-fpm.d/zz-docker.conf
[global]
daemonize = no

[www]
user = www-data
group = www-data
listen = /var/run/uwu/php8.3-fpm.sock
listen.mode = 0666
EOF
COPY <<EOF /etc/nginx/sites-enabled/default.conf
server {
    server_name localhost;
    root /var/www/uwu/public;

    location / {
        # try to serve file directly, fallback to index.php
        try_files \$uri /index.php\$is_args\$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/uwu/php8.3-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;

        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT \$document_root;
        # Prevents URIs that include the front controller. This will 404:
        # http://example.com/index.php/some-path
        # Remove the internal directive to allow URIs like this
        internal;
    }

    # return 404 for all other php files not matching the front controller
    # this prevents access to other php files you don't want to be accessible.
    location ~ \.php$ {
        return 404;
    }

    error_log /var/log/nginx/project_error.log;
    access_log /var/log/nginx/project_access.log;
}
EOF
HEALTHCHECK CMD socat -u OPEN:/dev/null UNIX-CONNECT:/var/run/uwu/php8.3-fpm.sock;
ENTRYPOINT ["/entrypoint.sh"]
USER root