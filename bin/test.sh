#!/usr/bin/env bash

#
# This file is part of the Fidry\AliceDataFixtures package.
#
# (c) Théo FIDRY <theo.fidry@gmail.com>
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#

export INFO_COLOR="\e[34m"
export NO_COLOR="\e[0m"

log() {
    local message=$1;
    echo -en "${INFO_COLOR}${message}${NO_COLOR}\n";
}

set -ex

log "Core library"
vendor/bin/phpunit -c phpunit.xml.dist


log "Doctrine bridge"
mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
mysql -u root -e "CREATE DATABASE fidry_alice_data_fixtures;"
vendor-bin/doctrine/bin/doctrine o:s:c

vendor-bin/doctrine/bin/phpunit -c phpunit_doctrine.xml.dist


log "Eloquent bridge"
mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
mysql -u root -e "CREATE DATABASE fidry_alice_data_fixtures;"
php bin/eloquent_migrate

vendor-bin/eloquent/bin/phpunit -c phpunit_eloquent.xml.dist


log "Symfony bridge"
mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
rm -rf fixtures/Bridge/Symfony/cache/*

vendor-bin/symfony/bin/phpunit -c phpunit_symfony.xml.dist


log "Symfony with Doctrine"
mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
mysql -u root -e "CREATE DATABASE fidry_alice_data_fixtures;"
rm -rf fixtures/Bridge/Symfony/cache/*
php bin/console d:s:c -k=DoctrineKernel

vendor-bin/symfony/bin/phpunit -c phpunit_symfony_doctrine.xml.dist


log "Symfony with Eloquent"
mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
mysql -u root -e "CREATE DATABASE fidry_alice_data_fixtures;"
rm -rf fixtures/Bridge/Symfony/cache/*
php bin/console eloquent:migrate:install -k=EloquentKernel

vendor-bin/eloquent/bin/phpunit -c phpunit_symfony_eloquent.xml.dist
