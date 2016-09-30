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

# Core library
vendor/bin/phpunit -c phpunit.xml.dist


# Doctrine bridge
mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
mysql -u root -e "CREATE DATABASE fidry_alice_data_fixtures;"
vendor-bin/doctrine/vendor/doctrine/orm/bin/doctrine o:s:c

vendor-bin/doctrine/vendor/phpunit/phpunit/phpunit -c phpunit_doctrine.xml.dist


# Eloquent bridge
mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
mysql -u root -e "CREATE DATABASE fidry_alice_data_fixtures;"
php bin/eloquent_migrate

vendor-bin/eloquent/vendor/phpunit/phpunit/phpunit -c phpunit_eloquent.xml.dist


# Symfony bridge
mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
rm -rf fixtures/Bridge/Symfony/cache/*

vendor-bin/symfony/vendor/phpunit/phpunit/phpunit -c phpunit_symfony.xml.dist


# Symfony with Doctrine
mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
mysql -u root -e "CREATE DATABASE fidry_alice_data_fixtures;"
rm -rf fixtures/Bridge/Symfony/cache/*
php bin/console d:s:c -k=DoctrineKernel

vendor-bin/symfony/vendor/phpunit/phpunit/phpunit -c phpunit_symfony_doctrine.xml.dist


# Symfony with Eloquent
mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
mysql -u root -e "CREATE DATABASE fidry_alice_data_fixtures;"
rm -rf fixtures/Bridge/Symfony/cache/*
php bin/console eloquent:migrate:install -k=EloquentKernel

vendor-bin/eloquent/vendor/phpunit/phpunit/phpunit -c phpunit_eloquent.xml.dist

mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
mysql -u root -e "CREATE DATABASE fidry_alice_data_fixtures;"
rm -rf fixtures/Bridge/Symfony/cache/*
php bin/console d:s:c

vendor-bin/symfony/vendor/phpunit/phpunit/phpunit -c phpunit_symfony.xml.dist
