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

namespace Fidry\AliceDataFixtures\Bridge\Symfony\ProxyManager\Eloquent;

use Fidry\AliceDataFixtures\Bridge\Eloquent\Persister\ModelPersister;
use Fidry\AliceDataFixtures\Bridge\Eloquent\Purger\ModelPurger;
use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundleTest as NakedFidryAliceDataFixturesBundleTest;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\EloquentKernel;
use Fidry\AliceDataFixtures\Loader\PersisterLoader;
use Fidry\AliceDataFixtures\Loader\PurgerLoader;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @coversNothing
 */
class FidryAliceDataFixturesBundleTest extends NakedFidryAliceDataFixturesBundleTest
{
    protected KernelInterface $kernel;

    public function setUp(): void
    {
        $this->kernel = EloquentKernel::create();
        $this->kernel->boot();
    }

    /**
     * @group legacy
     *
     * @expectedDepreaction The service "fidry_alice_data_fixtures.loader.multipass_file" is deprecated and will be removed in future versions.
     */
    public function testServiceRegistration(): void
    {
        parent::testServiceRegistration();

        self::assertInstanceOf(
            ModelPurger::class,
            $this->kernel->getContainer()->get('fidry_alice_data_fixtures.persistence.purger.eloquent.model_purger')
        );

        self::assertInstanceOf(
            ModelPersister::class,
            $this->kernel->getContainer()->get('fidry_alice_data_fixtures.persistence.persister.eloquent.model_persister')
        );

        self::assertInstanceOf(
            PersisterLoader::class,
            $this->kernel->getContainer()->get('fidry_alice_data_fixtures.eloquent.persister_loader')
        );

        self::assertInstanceOf(
            PurgerLoader::class,
            $this->kernel->getContainer()->get('fidry_alice_data_fixtures.eloquent.purger_loader')
        );
    }
}
