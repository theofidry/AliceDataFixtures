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

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\Purger\ORMPurger as DoctrineOrmPurger;
use Fidry\AliceDataFixtures\Bridge\Symfony\Entity\Dummy;
use Fidry\AliceDataFixtures\Bridge\Symfony\Entity\Group;
use Fidry\AliceDataFixtures\Bridge\Symfony\Entity\User;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\DoctrineKernel;
use Fidry\AliceDataFixtures\LoaderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @coversNothing
 */
class ORMLoaderIntegrationTest extends TestCase
{
    private KernelInterface $kernel;

    private LoaderInterface $loader;

    private Registry $doctrine;

    private static string $seed;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$seed = uniqid();
    }

    public function setUp(): void
    {
        $this->kernel = new DoctrineKernel(static::$seed, true);
        $this->kernel->boot();

        $this->loader = $this->kernel->getContainer()->get('fidry_alice_data_fixtures.doctrine.persister_loader');
        $this->doctrine = $this->kernel->getContainer()->get('doctrine');
    }

    public function tearDown(): void
    {
        $purger = new DoctrineOrmPurger($this->doctrine->getManager());
        $purger->purge();

        $this->kernel->shutdown();
        unset($this->kernel);
    }

    public function testLoadAFile(): void
    {
        $this->loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/dummy.yml',
        ]);

        $result = $this->doctrine->getRepository(Dummy::class)->findAll();

        $this->assertEquals(1, count($result));
    }

    public function testLoadAFileWithPurger(): void
    {
        $dummy = new Dummy();
        $dummyManager = $this->doctrine->getManager();
        $dummyManager->persist($dummy);
        $dummyManager->flush();
        $dummyManager->clear();

        // Disable foreign keys check
        // This is usually a bad idea as you have to deal *how* your entities are deleted
        // And doing that can lead to broken entities
        // However in this context we unset ALL entities and it's for testing purpose
        // Not a real application where deleting an application should be handled properly
        $this->doctrine->getConnection()->exec('SET FOREIGN_KEY_CHECKS=0;');
        $loader = $this->kernel->getContainer()->get('fidry_alice_data_fixtures.doctrine.purger_loader');
        $loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/dummy.yml',
        ]);
        $this->doctrine->getConnection()->exec('SET FOREIGN_KEY_CHECKS=1;');

        $result = $this->doctrine->getRepository(Dummy::class)->findAll();

        $this->assertEquals(1, count($result));
    }

    public function testBidirectionalRelationships(): void
    {
        $this->loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/user_group.yml',
        ]);

        $users = $this->doctrine->getRepository(User::class)->findAll();
        $groups = $this->doctrine->getRepository(Group::class)->findAll();

        $this->assertEquals(5, count($users));
        $this->assertEquals(5, count($groups));
    }

    public function testBidirectionalRelationshipsDeclaredInDifferentFiles(): void
    {
        $this->loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/user_with_group.yml',
            __DIR__.'/../../../../fixtures/fixture_files/group.yml',
        ]);

        $users = $this->doctrine->getRepository(User::class)->findAll();
        $groups = $this->doctrine->getRepository(Group::class)->findAll();

        $this->assertEquals(5, count($users));
        $this->assertEquals(5, count($groups));
    }

    public function testBidirectionalRelationshipsDeclaredInDifferentFilesWithCyclingDependence(): void
    {
        $this->loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/user_with_group.yml',
            __DIR__.'/../../../../fixtures/fixture_files/group_with_user.yml',
        ]);

        $users = $this->doctrine->getRepository(User::class)->findAll();
        $groups = $this->doctrine->getRepository(Group::class)->findAll();

        $this->assertEquals(5, count($users));
        $this->assertEquals(5, count($groups));
    }
}
