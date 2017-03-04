#!/usr/bin/env bash
set -Eeux
if [ ! -f $HOME/.composer/vendor/bin/wp ]; then
    curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar > $HOME/.composer/cache/wp-cli.phar
    chmod a+x $HOME/.composer/cache/wp-cli.phar
    mkdir -p $HOME/.composer/vendor/bin
    cp $HOME/.composer/cache/wp-cli.phar $HOME/.composer/vendor/bin/wp
fi

composer require "facebook/webdriver"
composer require "browserstack/browserstack-local"
mysqladmin create testing --user=root
rm -rf /tmp/wordpress
mkdir /tmp/wordpress
wp core download --path=/tmp/wordpress --version=$WP_VERSION
wp core config --path=/tmp/wordpress --dbname=testing --dbuser=root
wp core install --path=/tmp/wordpress --url=127.0.0.1:8000 --title=aquiestamostest --admin_user=aquiestamos --admin_password=aquiestamos --admin_email=example@example.com --skip-email

./bin/build-zip /tmp/aquiestamos.zip
wp plugin install --path=/tmp/wordpress/ --activate /tmp/aquiestamos.zip
wp post --path=/tmp/wordpress create --post_content='[ae-map]' --post_type=post --post_title='A sample post' --post_status=publish

wp server --path=/tmp/wordpress --host=127.0.0.1 --port=8000 &
mv phpunit.browserstack.xml.dist phpunit.xml.dist
rm phpunit.unit.xml.dist
