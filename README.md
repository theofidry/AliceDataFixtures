AliceDataFixtures
===========

[Alice](https://github.com/nelmio/alice) 3.x no longer ships with a persistence layer, so this library provides one!

[![Package version](https://img.shields.io/packagist/vpre/theofidry/alice-data-fixtures.svg?style=flat-square)](https://packagist.org/packages/theofidry/alice-data-fixtures)
[![Build Status](https://img.shields.io/travis/theofidry/AliceDataFixtures/master.svg?style=flat-square)](https://travis-ci.org/theofidry/AliceDataFixtures?branch=master)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/hautelook/AliceBundle?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)
[![license](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](LICENSE)


## Documentation

1. [Install](#installation)
    1. [Symfony Bundle](#symfony)
1. [Basic usage](#basic-usage)
1. Advanced usage
    1. [Processors](doc/processors.md)
    1. [Purge data](doc/purge_data.md)
1. [Contributing](#contributing)


## Installation

First you need install appropriate database managers (if you didn't install it yet), according to your project requirements.
Check the documentation [here](doc/install.md).

You can use [Composer](https://getcomposer.org/) to install the bundle to your project:

```bash
composer require --dev hautelook/alice-bundle doctrine/data-fixtures
```

Then you can install the library:

```bash
composer require --dev theofidry/alice-persistence

# If you are using Doctrine ORM:

composer require --dev theofidry/alice-persistence doctrine/orm doctrine/data-fixtures

```

If you are working with Doctrine ORM, you need to install the following packages as well:


### Symfony

This library ships with a Symfony bundle. To use it with Doctrine do not forget to install `doctrine/doctrine-bundle`
and enable the `DoctrineBundle` (done by default in Symfony Standard Edition).

Then, enable the bundle by updating your `app/AppKernel.php` file to enable the bundle:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    //...
    if (in_array($this->getEnvironment(), ['dev', 'test'])) {
        //...
        $bundles[] = new Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle();
    }

    return $bundles;
}
```


## Basic usage

Assuming you are using [Doctrine](http://www.doctrine-project.org/projects/orm.html), make sure you
have the [`doctrine/doctrine-bundle`](https://github.com/doctrine/DoctrineBundle) and [`doctrine/data-fixtures`](https://github.com/doctrine/data-fixtures) packages installed.

Then create a fixture file in `src/AppBundle/Resources/fixtures`:

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

$loader = $container->get('fidry_alice_data_fixtures.loader');
$objects = $loader->load($files);

// $objects is now an array of persisted `Dummy` and `RelatedDummy`
```

[Check more advanced doc](#documentation).


## Contributing

Clone the project, install the dependencies and use `bin/test.sh` to run all the tests!

