AliceDataFixtures
===========

[Alice](https://github.com/nelmio/alice) 3.x no longer ships with a persistence layer, so this library provides one!

[![Package version](https://img.shields.io/packagist/vpre/theofidry/alice-data-fixtures.svg?style=flat-square)](https://packagist.org/packages/theofidry/alice-data-fixtures)
[![Build Status](https://img.shields.io/travis/theofidry/AliceDataFixtures/master.svg?style=flat-square)](https://travis-ci.org/theofidry/AliceDataFixtures?branch=master)
[![Slack](https://img.shields.io/badge/slack-%23alice--fixtures-red.svg?style=flat-square)](https://symfony-devs.slack.com/shared_invite/MTYxMjcxMjc0MTc5LTE0OTA3ODE4OTQtYzc4NWVmMzRmZQ)
[![license](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](LICENSE)


## Documentation

1. [Installation](#installation)
    1. [Symfony Bundle](#symfony)
        1. [Doctrine ORM](#doctrine-orm)
        1. [Eloquent ORM](#eloquent-orm)
        1. [Configuration](#configuration)
1. [Basic usage](#basic-usage)
1. Advanced usage
    1. [Processors](doc/processors.md)
    1. [Purge data](doc/purge_data.md)
1. [Contributing](#contributing)


## Installation

You can use [Composer](https://getcomposer.org/) to install the library to your project:

```bash
composer require --dev theofidry/alice-data-fixtures:^1.0@beta nelmio/alice:^3.0@rc

# with Doctrine
composer require --dev theofidry/alice-data-fixtures:^1.0@beta \
  nelmio/alice:^3.0@rc \
  doctrine/orm:^2.5 \
  doctrine/data-fixtures

# with Eloquent
composer require --dev theofidry/alice-data-fixtures:^1.0@beta \
  nelmio/alice:^3.0@rc \
  illuminate/database:~5.3.0
```


### Symfony

This library ships with a Symfony bundle `FidryAliceDataFixturesBundle`.


#### Doctrine ORM

To use it with Doctrine do not forget to install `doctrine/doctrine-bundle`
and enable the `DoctrineBundle` (done by default in Symfony Standard Edition).

Then, enable the bundle by updating your `app/AppKernel.php` file to enable the bundle:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        // ...
        new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
    ];

    if (in_array($this->getEnvironment(), ['dev', 'test'])) {
        //...
        $bundles[] = new Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle();
        $bundles[] = new Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle();
    }

    return $bundles;
}
```

#### Eloquent ORM

To use it with Eloquent do not forget to install `illuminate/database` and
`WouterJEloquentBundle` (`wouterj/eloquent-bundle`).

Then, enable the bundle by updating your `app/AppKernel.php` file to enable the bundle:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        // ...
        new WouterJ\EloquentBundle\WouterJEloquentBundle(),
    ];

    if (in_array($this->getEnvironment(), ['dev', 'test'])) {
        //...
        $bundles[] = new Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle();
        $bundles[] = new Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle();
    }

    //...
    if (in_array($this->getEnvironment(), ['dev', 'test'])) {
        //...
        $bundles[] = new Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle();
    }

    return $bundles;
}
```


### Configuration

The full configuration reference is:

```yaml
# app/config/config.yml

# Default config
fidry_alice_data_fixtures:
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
$files = [
    'path/to/src/AppBundle/Resources/fixtures/dummy.yml',
    'path/to/src/AppBundle/Resources/fixtures/related_dummy.yml',
];

$loader = $container->get('fidry_alice_data_fixtures.doctrine.persister_loader'); // For Doctrine ORM
$loader = $container->get('fidry_alice_data_fixtures.doctrine_mongodb.persister_loader'); // For Doctrine MongoDB ODM
$loader = $container->get('fidry_alice_data_fixtures.doctrine_phpcr.persister_loader'); // For Doctrine PHPCR
$loader = $container->get('fidry_alice_data_fixtures.eloquent.persister_loader'); // For Eloquent ORM
$objects = $loader->load($files);

// $objects is now an array of persisted `Dummy` and `RelatedDummy`
```

[Check more advanced doc](#documentation).


## Contributing

Clone the project, install the dependencies and use `bin/test.sh` to run all the tests!

