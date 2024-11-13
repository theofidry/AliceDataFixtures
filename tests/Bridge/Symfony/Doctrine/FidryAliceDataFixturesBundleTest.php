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

namespace Fidry\AliceDataFixtures\Bridge\Symfony\Doctrine;

use Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger;
use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundleTest as NakedFidryAliceDataFixturesBundleTest;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\DoctrineKernel;
use Fidry\AliceDataFixtures\Loader\PersisterLoader;
use Fidry\AliceDataFixtures\Loader\PurgerLoader;

/**
 * @coversNothing
 */
class FidryAliceDataFixturesBundleTest extends NakedFidryAliceDataFixturesBundleTest
{
    public function setUp(): void
    {
        $this->kernel = DoctrineKernel::create();
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

        self::assertServiceIsInstanceOf(
            Purger::class,
            'fidry_alice_data_fixtures.persistence.purger.doctrine.orm_purger'
        );

        self::assertServiceIsInstanceOf(
            ObjectManagerPersister::class,
            'fidry_alice_data_fixtures.persistence.persister.doctrine.object_manager_persister'
        );

        self::assertServiceIsInstanceOf(
            PersisterLoader::class,
            'fidry_alice_data_fixtures.doctrine.persister_loader'
        );

        self::assertServiceIsInstanceOf(
            PurgerLoader::class,
            'fidry_alice_data_fixtures.doctrine.purger_loader'
        );
    }
}
