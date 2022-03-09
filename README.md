AliceDataFixtures
===========

[Alice](https://github.com/nelmio/alice) 3.x no longer ships with a persistence layer, so this library provides one!

[![Package version](https://img.shields.io/packagist/v/theofidry/alice-data-fixtures.svg?style=flat-square)](https://packagist.org/packages/theofidry/alice-data-fixtures)
[![Build Status](https://github.com/theofidry/AliceDataFixtures/workflows/Test/badge.svg?branch=master)](https://github.com/theofidry/AliceDataFixtures/actions?query=branch%3Amaster)
[![Slack](https://img.shields.io/badge/slack-%23alice--fixtures-red.svg?style=flat-square)](https://symfony-devs.slack.com/shared_invite/MTYxMjcxMjc0MTc5LTE0OTA3ODE4OTQtYzc4NWVmMzRmZQ)
[![license](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](LICENSE)


Supports:

* Symfony 4.4+, 5.4+, 6.0+
* Doctrine ORM 2.5+
* Doctrine ODM 2.0+
* Doctrine PHPCR 1.4+
* Eloquent 8.12+


## Documentation

1. [Installation](doc/installation.md/#installation)
    1. [Without Symfony](doc/installation.md/#without-symfony)
    1. [Symfony with Flex](doc/installation.md/#symfony-with-flex)
    1. [Symfony without flex](doc/installation.md/#symfony-without-flex)
        1. [Doctrine ORM](doc/installation.md/#doctrine-orm)
        1. [Doctrine ODM](doc/installation.md/#doctrine-odm)
        1. [Doctrine PHPCR](doc/installation.md/#doctrine-phpcr)
        1. [Eloquent ORM](doc/installation.md/#eloquent-orm)
        1. [Configuration](doc/installation.md/#configuration)
1. [Basic usage](#basic-usage)
1. [Configuration](#configuration)
1. [Advanced usage](doc/advanced-usage.md#advanced-usage)
    1. [Processors](doc/advanced-usage.md#processors)
    1. [Exclude tables from purge](doc/advanced-usage.md#exclude-tables-from-purge)
    1. [Usage in tests](doc/advanced-usage.md#usage-in-tests)
        1. [PHPUnit](doc/advanced-usage.md#phpunit)
        1. [Behat](doc/advanced-usage.md#behat)
1. [Contributing](#contributing)


## Configuration

The full configuration reference is:

```yaml
# app/config/config.yml

# Default config
fidry_alice_data_fixtures:
    default_purge_mode: ~ # default is "delete" but you can change it to "truncate" or "no_purge"
    db_drivers:
        doctrine_orm: ~
        doctrine_mongodb_odm: ~
        doctrine_phpcr_odm: ~
        eloquent_orm: ~
```

For each driver, is the appropriate bundle is detected, e.g. DoctrineORMBundle for Doctrine and WouterJEloquentBundle
for Eloquent, the services related to those driver will be enabled. If you want to skip those checks you can turn
a specific driver to `true` instead. If you want to disable a specific driver, simply force the value `false` instead.


## Basic usage

Create a fixture file in `src/AppBundle/Resources/fixtures`:

```yaml
# src/AppBundle/Resources/fixtures/dummy.yml

AppBundle\Entity\Dummy:
    dummy_{1..10}:
        name: <name()>
        related_dummy: '@related_dummy*'
```

```yaml
# src/AppBundle/Resources/fixtures/related_dummy.yml

AppBundle\Entity\RelatedDummy:
    related_dummy_{1..10}:
        name: <name()>
```

Then you can load those files using a `LoaderInterface`:

```php
<?php

$files = [
    'path/to/src/AppBundle/Resources/fixtures/dummy.yml',
    'path/to/src/AppBundle/Resources/fixtures/related_dummy.yml',
];

// Choose your loader
$loader = $container->get('fidry_alice_data_fixtures.loader.doctrine');         // For Doctrine ORM
$loader = $container->get('fidry_alice_data_fixtures.loader.doctrine_mongodb'); // For Doctrine MongoDB ODM
$loader = $container->get('fidry_alice_data_fixtures.loader.doctrine_phpcr');   // For Doctrine PHPCR
$loader = $container->get('fidry_alice_data_fixtures.loader.eloquent');         // For Eloquent ORM

// Purge the objects, create PHP objects from the fixture files and persist them
$objects = $loader->load($files);

// $objects is now an array of persisted `Dummy` and `RelatedDummy`
```

**Warning**: loading the objects does not trigger a `clear()`. This means if
you are relying on some Doctrine life-cycle events in your tests, some may not
be triggered as expected. See #84 For more information.


## Advanced Usage

Check the [advance usage entry](doc/advanced-usage.md).


## Contributing

Clone the project

To launch Docker containers for databases, run `make start_databases`

Run tests with `make test`.

To stop containers for databases, run `make stop_databases`

