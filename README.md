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
1. [Processors](#processors)
1. [Contributing](#contributing)


## Installation

You can use [Composer](https://getcomposer.org/) to install the library to your project:

```bash
composer require --dev theofidry/alice-data-fixtures:^1.0@beta nelmio/alice:^3.1

# with Doctrine
composer require --dev theofidry/alice-data-fixtures:^1.0@beta \
  nelmio/alice:^3.1 \
  doctrine/orm:^2.5 \
  doctrine/data-fixtures

# with Eloquent
composer require --dev theofidry/alice-data-fixtures:^1.0@beta \
  nelmio/alice:^3.1 \
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

// Choose your loader
$loader = $container->get('fidry_alice_data_fixtures.loader.doctrine');         // For Doctrine ORM
$loader = $container->get('fidry_alice_data_fixtures.loader.doctrine_mongodb'); // For Doctrine MongoDB ODM
$loader = $container->get('fidry_alice_data_fixtures.loader.doctrine_phpcr');   // For Doctrine PHPCR
$loader = $container->get('fidry_alice_data_fixtures.loader.eloquent');         // For Eloquent ORM

// Purge the objects, create PHP objects from the fixture files and persist them
$objects = $loader->load($files);

// $objects is now an array of persisted `Dummy` and `RelatedDummy`
```


## Processors

Processors allow you to process objects before and/or after they are persisted. Processors
must implement the [`Fidry\AliceDataFixtures\ProcessorInterface`](src/ProcessorInterface.php).

Here is an example where we may use this feature to make sure passwords are properly
hashed on a `User`:

```php
namespace MyApp\DataFixtures\Processor;

use Fidry\AliceDataFixtures\ProcessorInterface;
use MyApp\Hasher\PasswordHashInterface;
use User;

final class UserProcessor implements ProcessorInterface
{
    private $passwordHasher;

    public function __construct(PasswordHashInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @inheritdoc
     */
    public function preProcess($object)
    {
        if (false === $object instanceof User) {
            return;
        }

        $object->password = $this->passwordHasher->hash($object->password);
    }

    /**
     * @inheritdoc
     */
    public function postProcess($object)
    {
        // do nothing
    }
}
```

In Symfony, if you wish to register the processor above you need to tag it with the
`fidry_alice_data_fixtures.processor` tag:

```yaml
# app/config/services.yml

services:
    AppBundle\DataFixtures\Processor\UserProcessor:
        arguments:
          - '@password_hasher'
        tags: [ { name: fidry_alice_data_fixtures.processor } ]
```


## Contributing

Clone the project, install the dependencies and use `bin/test.sh` to run all the tests!

