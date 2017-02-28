#!/usr/bin/env bash
set -Eeux
composer global require wp-coding-standards/wpcs
phpcs --config-set installed_paths $HOME/.composer/vendor/wp-coding-standards/wpcs
mv phpunit.unit.xml.dist phpunit.xml.dist
rm phpunit.browserstack.xml.dist
