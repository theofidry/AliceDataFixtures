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
use Doctrine\Persistence\ManagerRegistry;
use Fidry\AliceDataFixtures\Bridge\Symfony\MongoDocument\Dummy;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\DoctrineMongodbKernel;
use Fidry\AliceDataFixtures\LoaderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @coversNothing
 *
 * @requires extension mongodb
 */
class MongodbLoaderIntegrationTest extends TestCase
{
    private KernelInterface $kernel;

    private LoaderInterface $loader;

    private ManagerRegistry $doctrine;

    private static string $seed;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$seed = uniqid();
    }

    public function setUp(): void
    {
        $this->kernel = new DoctrineMongodbKernel(static::$seed, true);
        $this->kernel->boot();

        $this->loader = $this->kernel->getContainer()->get('fidry_alice_data_fixtures.doctrine_mongodb.persister_loader');
        $this->doctrine = $this->kernel->getContainer()->get('doctrine_mongodb');
    }

    public function tearDown(): void
    {
        $purger = new MongoDBPurger($this->doctrine->getManager());
        $purger->purge();

        $this->kernel->shutdown();
        unset($this->kernel);
    }

    public function testLoadAFile(): void
    {
        $this->loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/mongodb_dummy.yml',
        ]);

        $result = $this->doctrine->getRepository(Dummy::class)->findAll();

        $this->assertCount(1, $result);
    }

    public function testLoadAFileWithPurger(): void
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

        $this->assertCount(1, $result);
    }
}
