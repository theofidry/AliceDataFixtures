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

refreshPhpcr() {
    mysql -u root -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures; CREATE DATABASE fidry_alice_data_fixtures;"
    php vendor-bin/doctrine_phpcr/bin/phpcrodm jackalope:init:dbal --force
    php vendor-bin/doctrine_phpcr/bin/phpcrodm doctrine:phpcr:register-system-node-types
}

refreshMongodb() {
    mongo fidry_alice_data_fixtures --eval "db.dropDatabase();"
}

set -ex

if [ -n "$COVERAGE" ]; then
    PHPUNIT_PREFIX="phpdbg -qrr"
else
    PHPUNIT_PREFIX="php -d zend.enable_gc=0"
fi

#log "Core library"
#PHPUNIT_CONFIG=phpunit.xml.dist
#php -d zend.enable_gc=0 vendor-bin/covers-validator/bin/covers-validator -c $PHPUNIT_CONFIG
#$PHPUNIT_PREFIX bin/phpunit -c $PHPUNIT_CONFIG $PHPUNIT_FLAGS


#log "Doctrine bridge"
#refreshDatabase
#vendor-bin/doctrine/bin/doctrine o:s:c
#
#PHPUNIT_CONFIG=phpunit_doctrine.xml.dist
#$PHPUNIT_PREFIX vendor-bin/doctrine/bin/phpunit -c $PHPUNIT_CONFIG $PHPUNIT_FLAGS

#log "Doctrine Mongodb ODM bridge"
#refreshMongodb
#PHPUNIT_CONFIG=phpunit_doctrine.xml.dist
#$PHPUNIT_PREFIX vendor-bin/doctrine_mongodb/bin/phpunit -c phpunit_doctrine_mongodb.xml.dist $PHPUNIT_FLAGS

#log "Doctrine Mongodb PHPCR bridge"
#refreshPhpcr
#PHPUNIT_CONFIG=phpunit_doctrine_phpcr.xml.dist
#$PHPUNIT_PREFIX vendor-bin/doctrine_phpcr/bin/phpunit -c $PHPUNIT_CONFIG $PHPUNIT_FLAGS


#log "Eloquent bridge"
#refreshDatabase
#php bin/eloquent_migrate
#
#PHPUNIT_CONFIG=phpunit_eloquent.xml.dist
#$PHPUNIT_PREFIX vendor-bin/eloquent/bin/phpunit -c $PHPUNIT_CONFIG $PHPUNIT_FLAGS


#log "Symfony bridge"
#refreshDatabase
#rm -rf fixtures/Bridge/Symfony/cache/*
#
#PHPUNIT_CONFIG=phpunit_symfony.xml.dist
#php -d zend.enable_gc=0 vendor-bin/covers-validator/bin/covers-validator -c $PHPUNIT_CONFIG
#$PHPUNIT_PREFIX vendor-bin/symfony/bin/phpunit -c $PHPUNIT_CONFIG $PHPUNIT_FLAGS


#log "Symfony with Doctrine"
#refreshDatabase
#refreshMongodb
#refreshPhpcr
#rm -rf fixtures/Bridge/Symfony/cache/*
#php bin/console d:s:c -k=DoctrineKernel
#
#PHPUNIT_CONFIG=phpunit_symfony_doctrine.xml.dist
#$PHPUNIT_PREFIX vendor-bin/symfony/bin/phpunit -c $PHPUNIT_CONFIG $PHPUNIT_FLAGS


#log "Symfony with Eloquent"
#refreshDatabase
#rm -rf fixtures/Bridge/Symfony/cache/*
#php bin/console eloquent:migrate:install -k=EloquentKernel
#
#PHPUNIT_CONFIG=phpunit_symfony_eloquent.xml.dist
#$PHPUNIT_PREFIX vendor-bin/symfony/bin/phpunit -c $PHPUNIT_CONFIG $PHPUNIT_FLAGS


#log "Symfony with Proxy Manager and Doctrine"
#refreshDatabase
#refreshMongodb
#refreshPhpcr
#rm -rf fixtures/Bridge/Symfony/cache/*
#php bin/console d:s:c -k=DoctrineKernel
#
#PHPUNIT_CONFIG=phpunit_symfony_proxy_manager_with_doctrine.xml.dist
#$PHPUNIT_PREFIX vendor-bin/proxy-manager/bin/phpunit -c $PHPUNIT_CONFIG $PHPUNIT_FLAGS

#
#log "Symfony with Proxy Manager"
#refreshDatabase
#rm -rf fixtures/Bridge/Symfony/cache/*
#php bin/console eloquent:migrate:install -k=EloquentKernel
#
#PHPUNIT_CONFIG=phpunit_symfony_proxy_manager_with_eloquent.xml.dist
#$PHPUNIT_PREFIX vendor-bin/proxy-manager/bin/phpunit -c $PHPUNIT_CONFIG $PHPUNIT_FLAGS
#
#
#log "Cleanup"
#rm -rf fixtures/Bridge/Symfony/cache/*
#refreshDatabase
#refreshMongodb
