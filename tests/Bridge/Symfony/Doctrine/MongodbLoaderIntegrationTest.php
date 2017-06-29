<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fidry\AlicePersistence\Bridge\Symfony\Doctrine;

use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\Common\Persistence\ManagerRegistry;
use Fidry\AliceDataFixtures\Bridge\Symfony\MongoDocument\Dummy;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\DoctrineMongodbKernel;
use Fidry\AliceDataFixtures\LoaderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class MongodbLoaderIntegrationTest extends TestCase
{
    /**
     * @var DoctrineMongodbKernel
     */
    private $kernel;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var int
     */
    private static $seed;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::$seed = uniqid();
    }

    public function setUp()
    {
        $this->kernel = new DoctrineMongodbKernel(static::$seed, true);
        $this->kernel->boot();

        $this->loader = $this->kernel->getContainer()->get('fidry_alice_data_fixtures.doctrine_mongodb.persister_loader');
        $this->doctrine = $this->kernel->getContainer()->get('doctrine_mongodb');
    }

    public function tearDown()
    {
        $purger = new MongoDBPurger($this->doctrine->getManager());
        $purger->purge();

        $this->kernel->shutdown();
        $this->kernel = null;
    }

    public function testLoadAFile()
    {
        $this->loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/mongodb_dummy.yml',
        ]);

        $result = $this->doctrine->getRepository(Dummy::class)->findAll();

        $this->assertEquals(1, count($result));
    }

    public function testLoadAFileWithPurger()
    {
        $dummy = new Dummy();
        $dummyManager = $this->doctrine->getManager();
        $dummyManager->persist($dummy);
        $dummyManager->flush();
        $dummyManager->clear();

        $loader = $this->kernel->getContainer()->get('fidry_alice_data_fixtures.doctrine_mongodb.purger_loader');
        $loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/mongodb_dummy.yml',
        ]);

        $result = $this->doctrine->getRepository(Dummy::class)->findAll();

        $this->assertEquals(1, count($result));
    }
}
