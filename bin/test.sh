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

mysql -u root -e "drop database fidry_alice_data_fixtures;"
mysql -u root -e "create database fidry_alice_data_fixtures;"
php vendor/bin/doctrine o:s:c

vendor/bin/phpunit -c phpunit.xml.dist
vendor-bin/doctrine/vendor/phpunit/phpunit/phpunit -c phpunit_doctrine.xml.dist
vendor-bin/symfony/vendor/phpunit/phpunit/phpunit -c phpunit_symfony.xml.dist
