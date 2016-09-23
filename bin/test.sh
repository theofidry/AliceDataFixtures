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

mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
rm -rf fixtures/Bridge/Symfony/cache/*

mysql -u root -e "CREATE DATABASE fidry_alice_data_fixtures;"
php bin/console d:s:c

vendor/bin/phpunit -c phpunit.xml.dist
vendor-bin/doctrine/vendor/phpunit/phpunit/phpunit -c phpunit_doctrine.xml.dist
vendor-bin/symfony/vendor/phpunit/phpunit/phpunit -c phpunit_symfony.xml.dist
