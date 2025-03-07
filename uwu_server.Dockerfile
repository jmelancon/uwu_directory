# syntax=docker/dockerfile:1-labs
FROM php:8.3-fpm AS private_install_pkgs
RUN apt-get update
RUN apt-get install libldap-dev libgmp-dev git unzip socat sqlite3 nginx libicu-dev python3 -y
RUN docker-php-ext-configure intl
RUN docker-php-ext-install ldap gmp intl

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

FROM private_all_src AS private_copy_config
COPY --chmod=755 sh/server_entrypoint.sh /entrypoint.sh
COPY container /

FROM private_copy_config AS private_generalize
RUN <<EOF
sed -E "s/(['\"])\/build/\1\$\{\{= UWU_BASE \}\}\/build/g" \
        /var/www/uwu/public/build/entrypoints.json > /var/www/uwu/public/build/entrypoints.json.template;
rm /var/www/uwu/public/build/entrypoints.json;

export STYLE_PATH=$(find /var/www/uwu/public/build -name "styles.*.css")
sed -E "s/(url\([\"']?)\/build/\1\$\{\{= UWU_BASE \}\}\/build/g" $STYLE_PATH > $STYLE_PATH.template
rm $STYLE_PATH
echo "$STYLE_PATH" > /var/www/uwu/style_location
EOF

FROM private_generalize AS uwu_server
HEALTHCHECK CMD socat -u OPEN:/dev/null UNIX-CONNECT:/var/run/uwu/php8.3-fpm.sock;
ENTRYPOINT ["/entrypoint.sh"]
USER root