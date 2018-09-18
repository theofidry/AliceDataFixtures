AliceDataFixtures
===========

[Alice](https://github.com/nelmio/alice) 3.x no longer ships with a persistence layer, so this library provides one!

[![Package version](https://img.shields.io/packagist/vpre/theofidry/alice-data-fixtures.svg?style=flat-square)](https://packagist.org/packages/theofidry/alice-data-fixtures)
[![Build Status](https://img.shields.io/travis/theofidry/AliceDataFixtures/master.svg?style=flat-square)](https://travis-ci.org/theofidry/AliceDataFixtures?branch=master)
[![Slack](https://img.shields.io/badge/slack-%23alice--fixtures-red.svg?style=flat-square)](https://symfony-devs.slack.com/shared_invite/MTYxMjcxMjc0MTc5LTE0OTA3ODE4OTQtYzc4NWVmMzRmZQ)
[![license](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](LICENSE)


Supports:

* Symfony 3.4, 4.0+
* Doctrine ORM 2.5+
* Doctrine ODM 1.2+
* Doctrine PHPCR 1.4+
* Eloquent 5.5+
* Propel 2


## Documentation

1. [Installation](#installation)
    1. [Symfony Bundle without flex](#symfony-without-flex)
        1. [Doctrine ORM](#doctrine-orm)
        1. [Doctrine ODM](#doctrine-odm)
        1. [Doctrine PHPCR](#doctrine-phpcr)
        1. [Eloquent ORM](#eloquent-orm)
        1. [Configuration](#configuration)
1. [Basic usage](#basic-usage)
1. [Advanced usage](#advanced-usage)
    1. [Processors](#processors)
    1. [Exclude tables from purge](#exclude-tables-from-purge)
    1. [Usage in tests](#usage-in-tests)
        1. [PHPUnit](#phpunit)
        1. [Behat](#behat)
1. [Contributing](#contributing)


## Installation

You can use [Composer](https://getcomposer.org/) to install the library to your project:

```bash
composer require --dev theofidry/alice-data-fixtures

#
# With Doctrine ORM
#

# with Symfony & Flex
composer require --dev theofidry/alice-data-fixtures \
                       doctrine-orm \
                       doctrine/data-fixtures

# without Symfony
composer require --dev theofidry/alice-data-fixtures \
                       doctrine/orm \
                       doctrine/data-fixtures


#
# With Doctrine ODM
#

composer require --dev theofidry/alice-data-fixtures \
                       alcaeus/mongo-php-adapter \
                       doctrine/data-fixtures \
                       doctrine/mongodb-odm

#
# With Doctrine PHPCR
#
composer require --dev theofidry/alice-data-fixtures \
                       doctrine/phpcr-odm \
                       jackalope/jackalope-doctrine-dbal

#
# With Eloquent
#
composer require --dev theofidry/alice-data-fixtures \
                       illuminate/database

#
# With Propel 2
#
composer require --dev theofidry/alice-data-fixtures \
                       propel/propel:^2.0@alpha
```


### Symfony without Flex

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


#### Doctrine ODM

To use it with Doctrine do not forget to install `doctrine/mongodb-odm`
and enable the `DoctrineMongoDBBundle`.

Then, enable the bundle by updating your `app/AppKernel.php` file to enable the bundle:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        // ...
        new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
    ];

    if (in_array($this->getEnvironment(), ['dev', 'test'])) {
        //...
        $bundles[] = new Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle();
        $bundles[] = new Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle();
    }

    return $bundles;
}
```


#### Doctrine PHPCR

To use it with Doctrine do not forget to install `doctrine/doctrine-bundle`
and enable the `DoctrineBundle` (done by default in Symfony Standard Edition)
and `DoctrinePHPCRBundle` (from `doctrine/phpcr-odm`)

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
        new Doctrine\Bundle\PHPCRBundle\DoctrinePHPCRBundle(),
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


## Advanced usage

### Processors

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
    public function preProcess(string $fixtureId, $object): void
    {
        if (false === $object instanceof User) {
            return;
        }

        $object->password = $this->passwordHasher->hash($object->password);
    }

    /**
     * @inheritdoc
     */
    public function postProcess(string $fixtureId, $object): void
    {
        // do nothing
    }
}
```

In Symfony, if you wish to register the processor above you need to tag it with the
`fidry_alice_data_fixtures.processor` tag unless you have `autoconfigure` enabled:

```yaml
# app/config/services.yml

services:
    _defaults:
        autoconfigure: true

    AppBundle\DataFixtures\Processor\UserProcessor:
        arguments:
          - '@password_hasher'
```

Without `autoconfigure`:

```yaml
# app/config/services.yml

services:
    AppBundle\DataFixtures\Processor\UserProcessor:
        arguments:
          - '@password_hasher'
        tags: [ { name: fidry_alice_data_fixtures.processor } ]
```


### Exclude tables from purge

You may have some view/read-only tables which should not be truncated when loading the
fixtures. To fix that, you can leverage the [Purger](https://github.com/doctrine/data-fixtures/pull/225)
to exclude them.

The purger for Doctrine is defined [here](https://github.com/theofidry/AliceDataFixtures/blob/master/src/Bridge/Doctrine/Purger/Purger.php). You
see that you can easily create your own purger based on it to retrieve the relevant metadata from the
object manager to exclude them:

```php
<?php declare(strict_types=1);

namespace Acme\Alice\Purger;

use Doctrine\Common\DataFixtures\Purger\ORMPurger as DoctrineOrmPurger;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface as DoctrinePurgerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use Nelmio\Alice\IsAServiceTrait;

/**
 * @final
 */
/* final */ class Purger implements PurgerInterface, PurgerFactoryInterface
{
    use IsAServiceTrait;

    private $manager;
    private $purger;

    public function __construct(ObjectManager $manager, PurgeMode $purgeMode = null)
    {
        $this->manager = $manager;
        $this->purger = static::createPurger($manager, $purgeMode);
    }
    // ...

    private static function createPurger(ObjectManager $manager, ?PurgeMode $purgeMode): DoctrinePurgerInterface
    {
        $metaData = $manager->getMetadataFactory()->getAllMetadata();

        $excluded = [];

        foreach ($metaData as $classMetadata) {
            /** @var ClassMetadata $classMetadata */
            if ($classMetadata->isReadOnly) {
                $excluded[] = $classMetadata->getTableName();
            }
        }

        $purger = new DoctrineOrmPurger($manager, $excluded);

        if (null !== $purgeMode) {
            $purger->setPurgeMode($purgeMode->getValue());
        }

        return $purger;
    }
}
```

In the case of Symfony with Doctrine ORM, you can the override the default purger factory used:

```yaml
// app/config/services.yaml

services:
    # Override the default service with your own
    fidry_alice_data_fixtures.persistence.purger_factory.doctrine:
        class: Acme\Alice\Purger\Purger
        arguments:
            - '@doctrine.orm.entity_manager'
```


### Usage in tests

It is common to load the fixtures for tests, but then you might need to reset your database between each tests. There is
two ways of doing it: purge between each test which works but comes with an overhead and warping the test in a
transaction which can be rollbacked at the end of the test. The second approach is usually faster but requires a
database that supports transactions and removes the ability to peak at the database in the middle of a test while debugging.


#### PHPUnit

There are several approaches, the following one is a simple one for test case with Symfony. Depending on your needs,
you might use the Symfony base TestCase or a PHPUnit listener.
In case of Symfony, take a look at [dmaicher/doctrine-test-bundle](https://github.com/dmaicher/doctrine-test-bundle)
which transparently provides a transactional run for your tests. In that case you can also disable default purge mode
by setting `default_purge_mode` [configuration option](#configuration) to `no_purge`.

If you are not using Symfony, this should still give you a pretty good idea on how to do it.

With purge:

```php
<?php declare(strict_types=1);

namespace Acme;

use Acme\Entity\Dummy;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\Purger\ORMPurger as DoctrineOrmPurger;
use PHPUnit\Framework\TestCase;

class FooTest extends TestCase
{
    /** @var AppKernel */
    private $kernel;
    /** @var LoaderInterface */
    private $loader;
    /** @var Registry */
    private $doctrine;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->kernel = new AppKernel('test', true);
        $this->kernel->boot();

        $this->loader = $this->kernel->getContainer()->get('fidry_alice_data_fixtures.loader.doctrine');
        $this->doctrine = $this->kernel->getContainer()->get('doctrine');
    }

    public function tearDown()
    {
        $purger = new DoctrineOrmPurger($this->doctrine->getManager());
        $purger->purge();

        $this->kernel->shutdown();
        $this->kernel = null;
    }

    public function testLoadAFile()
    {
        $this->loader->load([
            '/path/to/my/fixtures/file.yml',
        ]);

        $result = $this->doctrine->getRepository(Dummy::class)->findAll();

        $this->assertEquals(1, count($result));
    }
}
```

With transaction:

```php
<?php declare(strict_types=1);

namespace Acme;

use Acme\Entity\Dummy;
use Doctrine\Bundle\DoctrineBundle\Registry;
use PHPUnit\Framework\TestCase;

class FooTest extends TestCase
{
    /** @var AppKernel */
    private $kernel;
    /** @var LoaderInterface */
    private $loader;
    /** @var Registry */
    private $doctrine;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->kernel = new AppKernel('test', true);
        $this->kernel->boot();

        $this->loader = $this->kernel->getContainer()->get('fidry_alice_data_fixtures.loader.doctrine');
        $this->doctrine = $this->kernel->getContainer()->get('doctrine');
        
        $connection = $this->doctrine->getConnection();
        
        // If you are using auto-increment IDs, you might want to reset them. It is usually better to purge/reset
        // things at the beginning of a test so that in case of a failure, you are not ending up in a broken state.
        // With PostgreSQL:
        $connection->executeQuery('ALTER SEQUENCE dummy_sequence RESTART');
        // With MySQL:
        $connection->executeQuery('ALTER TABLE dummy AUTO_INCREMENT = 1');
        
        // Related to the possible failures - see the comment above, you might want to empty some tables here as well.
        // Maybe by using the purger like in the example above? Up to you.
        // It is also a good practice to clear all the repositories. How you collect all of the repositories: leveraging
        // the framework or manually is up to you.
        
        $connection->beginTransaction();
    }

    public function tearDown()
    {
        $this->doctrine->getConnection('default')->rollBack();

        $this->kernel->shutdown();
        $this->kernel = null;
    }

    public function testLoadAFile()
    {
        $this->loader->load([
            '/path/to/my/fixtures/file.yml',
        ]);

        $result = $this->doctrine->getRepository(Dummy::class)->findAll();

        $this->assertEquals(1, count($result));
    }
}
```

#### Behat

The idea is pretty much the same as for PHPUnit. You can register a context hooking on the events to start a transaction
at the beginning of a scenario and rollback at the end of it:

```php
<?php declare(strict_types=1);

namespace Acme;

use Behat\Behat\Context\Context;
use Doctrine\Bundle\DoctrineBundle\Registry;

class DatabaseContext implements Context
{
    private $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    /**
     * @BeforeScenario
     */
    public function clearRepositories()
    {
        // Related to the possible failures - see the comment above, you might want to empty some tables here as well.
        // Maybe by using the purger like in the example above? Up to you.
        // It is also a good practice to clear all the repositories. How you collect all of the repositories: leveraging
        // the framework or manually is up to you.
    }

    /**
     * @BeforeScenario
     */
    public function resetSequences()
    {
        $connection = $this->doctrine->getConnection();
                
        // If you are using auto-increment IDs, you might want to reset them. It is usually better to purge/reset
        // things at the beginning of a test so that in case of a failure, you are not ending up in a broken state.
        // With PostgreSQL:
        $connection->executeQuery('ALTER SEQUENCE dummy_sequence RESTART');
        // With MySQL:
        $connection->executeQuery('ALTER TABLE dummy AUTO_INCREMENT = 1');
    }

    /**
     * @BeforeScenario
     */
    public function beginPostgreSqlTransaction()
    {
        $this->doctrine->getConnection()->beginTransaction();
    }

    /**
     * @AfterScenario
     */
    public function rollbackPostgreSqlTransaction()
    {
        $this->doctrine->getConnection()->rollBack();
    }

}
```

## Contributing

Clone the project and run `make`.

