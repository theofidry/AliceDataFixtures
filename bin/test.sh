#!/usr/bin/env bash

#
# This file is part of the Fidry\AliceDataFixtures package.
#
# (c) Théo FIDRY <theo.fidry@gmail.com>
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#

set -ex

vendor/bin/phpunit -c phpunit.xml.dist

mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
mysql -u root -e "CREATE DATABASE fidry_alice_data_fixtures;"
vendor-bin/doctrine/vendor/doctrine/orm/bin/doctrine o:s:c

vendor-bin/doctrine/vendor/phpunit/phpunit/phpunit -c phpunit_doctrine.xml.dist

mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
mysql -u root -e "CREATE DATABASE fidry_alice_data_fixtures;"
rm -rf fixtures/Bridge/Symfony/cache/*
php bin/console d:s:c

vendor-bin/symfony/vendor/phpunit/phpunit/phpunit -c phpunit_symfony.xml.dist

mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
mysql -u root -e "CREATE DATABASE fidry_alice_data_fixtures;"
php bin/eloquent_migrate

vendor-bin/eloquent/vendor/phpunit/phpunit/phpunit -c phpunit_eloquent.xml.dist

