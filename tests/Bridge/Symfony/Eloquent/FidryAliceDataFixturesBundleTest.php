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

namespace Fidry\AliceDataFixtures\Bridge\Symfony\Eloquent;

use Fidry\AliceDataFixtures\Bridge\Eloquent\Persister\ModelPersister;
use Fidry\AliceDataFixtures\Bridge\Eloquent\Purger\ModelPurger;
use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundleTest as NakedFidryAliceDataFixturesBundleTest;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\EloquentKernel;
use Fidry\AliceDataFixtures\Loader\PersisterLoader;
use Fidry\AliceDataFixtures\Loader\PurgerLoader;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpKernel\KernelInterface;

#[CoversNothing]
class FidryAliceDataFixturesBundleTest extends NakedFidryAliceDataFixturesBundleTest
{
    protected KernelInterface $kernel;

    public function setUp(): void
    {
        $this->kernel = EloquentKernel::create();
        $this->kernel->boot();
    }

    /**
     * @expectedDepreaction The service "fidry_alice_data_fixtures.loader.multipass_file" is deprecated and will be removed in future versions.
     */
    #[Group('legacy')]
    public function testServiceRegistration(): void
    {
        parent::testServiceRegistration();

        self::assertServiceIsInstanceOf(
            ModelPurger::class,
            'fidry_alice_data_fixtures.persistence.purger.eloquent.model_purger'
        );

        self::assertServiceIsInstanceOf(
            ModelPersister::class,
            'fidry_alice_data_fixtures.persistence.persister.eloquent.model_persister'
        );

        self::assertServiceIsInstanceOf(
            PersisterLoader::class,
            'fidry_alice_data_fixtures.eloquent.persister_loader'
        );

        self::assertServiceIsInstanceOf(
            PurgerLoader::class,
            'fidry_alice_data_fixtures.eloquent.purger_loader'
        );
    }
}
