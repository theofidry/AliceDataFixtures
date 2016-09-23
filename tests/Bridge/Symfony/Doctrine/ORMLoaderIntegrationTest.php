<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AlicePersistence\Bridge\Symfony\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Fidry\AliceDataFixtures\Bridge\Symfony\Entity\Dummy;
use Fidry\AliceDataFixtures\Bridge\Symfony\Entity\Group;
use Fidry\AliceDataFixtures\Bridge\Symfony\Entity\User;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\DoctrineOrmKernel;
use Fidry\AliceDataFixtures\LoaderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group symfony
 * @group doctrine
 */
class ORMLoaderIntegrationTest extends TestCase
{
    /**
     * @var DoctrineOrmKernel
     */
    private $kernel;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var Registry
     */
    private $doctrine;

    public function setUp()
    {
        $this->kernel = new DoctrineOrmKernel('test', true);
        $this->kernel->boot();

        $this->loader = $this->kernel->getContainer()->get('fidry_alice_data_fixtures.loader');
        $this->doctrine = $this->kernel->getContainer()->get('doctrine');
    }

    public function tearDown()
    {
        $purger = new ORMPurger($this->doctrine->getManager());
        $purger->purge();

        $this->kernel->shutdown();
        $this->kernel = null;
    }

    public function testLoadAFile()
    {
        $this->loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/dummy.yml',
        ]);

        $result = $this->doctrine->getRepository(Dummy::class)->findAll();

        $this->assertEquals(1, count($result));
    }

    public function testBidirectionalRelationships()
    {
        $this->loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/user_group.yml',
        ]);

        $users = $this->doctrine->getRepository(User::class)->findAll();
        $groups = $this->doctrine->getRepository(Group::class)->findAll();

        $this->assertEquals(10, count($users));
        $this->assertEquals(10, count($groups));
    }
}
