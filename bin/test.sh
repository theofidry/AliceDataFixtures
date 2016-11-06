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

refreshDatabase() {
    mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures; CREATE DATABASE fidry_alice_data_fixtures;"
}

set -ex

if [ -n "$COVERAGE" ]; then
    PHPUNI_PREFIX="phpdbg -qrr"
fi

log "Core library"
$PHPUNI_PREFIX vendor/bin/phpunit -c phpunit.xml.dist $PHPUNIT_FLAGS


log "Doctrine bridge"
refreshDatabase
vendor-bin/doctrine/bin/doctrine o:s:c

$PHPUNI_PREFIX vendor-bin/doctrine/bin/phpunit -c phpunit_doctrine.xml.dist $PHPUNIT_FLAGS


log "Eloquent bridge"
refreshDatabase
php bin/eloquent_migrate

$PHPUNI_PREFIX vendor-bin/eloquent/bin/phpunit -c phpunit_eloquent.xml.dist $PHPUNIT_FLAGS


log "Symfony bridge"
refreshDatabase
rm -rf fixtures/Bridge/Symfony/cache/*

$PHPUNI_PREFIX vendor-bin/symfony/bin/phpunit -c phpunit_symfony.xml.dist $PHPUNIT_FLAGS


log "Symfony with Doctrine"
refreshDatabase
rm -rf fixtures/Bridge/Symfony/cache/*
php bin/console d:s:c -k=DoctrineKernel

#vendor-bin/symfony/bin/phpunit -c phpunit_symfony_doctrine.xml.dist $PHPUNIT_FLAGS


log "Symfony with Eloquent"
refreshDatabase
rm -rf fixtures/Bridge/Symfony/cache/*
php bin/console eloquent:migrate:install -k=EloquentKernel

$PHPUNI_PREFIX vendor-bin/eloquent/bin/phpunit -c phpunit_symfony_eloquent.xml.dist $PHPUNIT_FLAGS


log "Symfony with Proxy Manager and Doctrine"
refreshDatabase
rm -rf fixtures/Bridge/Symfony/cache/*
php bin/console d:s:c -k=DoctrineKernel

$PHPUNI_PREFIX vendor-bin/proxy-manager/bin/phpunit -c phpunit_symfony_proxy_manager_with_doctrine.xml.dist $PHPUNIT_FLAGS


log "Symfony with Proxy Manager"
refreshDatabase
rm -rf fixtures/Bridge/Symfony/cache/*
php bin/console eloquent:migrate:install -k=EloquentKernel

$PHPUNI_PREFIX vendor-bin/proxy-manager/bin/phpunit -c phpunit_symfony_proxy_manager_with_eloquent.xml.dist $PHPUNIT_FLAGS


log "Cleanup"
rm -rf fixtures/Bridge/Symfony/cache/*
refreshDatabase
