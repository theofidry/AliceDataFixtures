# Advanced usage

1. [Processors](#processors)
1. [Exclude tables from purge](#exclude-tables-from-purge)
1. [Usage in tests](#usage-in-tests)
    1. [PHPUnit](#phpunit)
    1. [Behat](#behat)


## Processors

Processors allow you to process objects before and/or after they are persisted. Processors
must implement the [`Fidry\AliceDataFixtures\ProcessorInterface`](../src/ProcessorInterface.php).

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


## Exclude tables from purge

You may have some view/read-only tables which should not be truncated when loading the
fixtures. To fix that, you can leverage the [Purger](https://github.com/doctrine/data-fixtures/pull/225)
to exclude them.

The purger for Doctrine is defined [here](../src/Bridge/Doctrine/Purger/Purger.php). You
see that you can easily create your own purger based on it to retrieve the relevant metadata from the
object manager to exclude them:

```php
<?php declare(strict_types=1);

namespace Acme\Alice\Purger;

use Doctrine\Common\DataFixtures\Purger\ORMPurger as DoctrineOrmPurger;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface as DoctrinePurgerInterface;
use Doctrine\Persistence\ObjectManager;
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


## Usage in tests

It is common to load the fixtures for tests, but then you might need to reset your database between each tests. There is
two ways of doing it: purge between each test which works but comes with an overhead and warping the test in a
transaction which can be rollbacked at the end of the test. The second approach is usually faster but requires a
database that supports transactions and removes the ability to peak at the database in the middle of a test while debugging.


### PHPUnit

There are several approaches, the following one is a simple one for test case with Symfony. Depending on your needs,
you might use the Symfony base TestCase or a PHPUnit listener.
In case of Symfony, take a look at [dmaicher/doctrine-test-bundle](https://github.com/dmaicher/doctrine-test-bundle)
which transparently provides a transactional run for your tests. In that case you can also disable default purge mode
by setting `default_purge_mode` [configuration option](../README.md#configuration) to `no_purge`.

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

### Behat

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

« [Configuration](../README.md#configuration) • [Back to README](../README.md#documentation) »
