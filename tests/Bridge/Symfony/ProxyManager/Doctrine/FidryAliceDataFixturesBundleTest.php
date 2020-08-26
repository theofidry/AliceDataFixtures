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

namespace Fidry\AliceDataFixtures\Bridge\Symfony\ProxyManager\Doctrine;

use Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ManagerRegistryPersister;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger;
use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundleTest as NakedFidryAliceDataFixturesBundleTest;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\DoctrineKernel;
use Fidry\AliceDataFixtures\Loader\PersisterLoader;
use Fidry\AliceDataFixtures\Loader\PurgerLoader;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @coversNothing
 */
class FidryAliceDataFixturesBundleTest extends NakedFidryAliceDataFixturesBundleTest
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->kernel = DoctrineKernel::create();
        $this->kernel->boot();
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        $this->kernel->shutdown();
    }

    /**
     * @group legacy
     * @expectedDepreaction The service "fidry_alice_data_fixtures.loader.multipass_file" is deprecated and will be removed in future versions.
     */
    public function testServiceRegistration()
    {
        parent::testServiceRegistration();

        $this->assertInstanceOf(
            Purger::class,
            $this->kernel->getContainer()->get('fidry_alice_data_fixtures.persistence.purger.doctrine.orm_purger')
        );

        $this->assertInstanceOf(
            ManagerRegistryPersister::class,
            $this->kernel->getContainer()->get('fidry_alice_data_fixtures.persistence.persister.doctrine.object_manager_persister')
        );

        $this->assertInstanceOf(
            PersisterLoader::class,
            $this->kernel->getContainer()->get('fidry_alice_data_fixtures.doctrine.persister_loader')
        );

        $this->assertInstanceOf(
            PurgerLoader::class,
            $this->kernel->getContainer()->get('fidry_alice_data_fixtures.doctrine.purger_loader')
        );
    }
}
