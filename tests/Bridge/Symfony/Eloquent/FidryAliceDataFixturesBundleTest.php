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

use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundleTest as NakedFidryAliceDataFixturesBundleTest;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\EloquentKernel;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @coversNothing
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FidryAliceDataFixturesBundleTest extends NakedFidryAliceDataFixturesBundleTest
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    public function setUp()
    {
        $this->kernel = EloquentKernel::create();
        $this->kernel->boot();
    }

    public function tearDown()
    {
        $this->kernel->shutdown();
    }

    public function testServiceRegistration()
    {
        parent::testServiceRegistration();

        $this->assertServiceIsInstanceOf(
            \Fidry\AliceDataFixtures\Bridge\Eloquent\Purger\ModelPurger::class,
            'fidry_alice_data_fixtures.persistence.purger.eloquent.model_purger'
        );

        $this->assertServiceIsInstanceOf(
            \Fidry\AliceDataFixtures\Bridge\Eloquent\Persister\ModelPersister::class,
            'fidry_alice_data_fixtures.persistence.persister.eloquent.model_persister'
        );

        $this->assertServiceIsInstanceOf(
            \Fidry\AliceDataFixtures\Loader\PersisterLoader::class,
            'fidry_alice_data_fixtures.eloquent.persister_loader'
        );

        $this->assertServiceIsInstanceOf(
            \Fidry\AliceDataFixtures\Loader\PurgerLoader::class,
            'fidry_alice_data_fixtures.eloquent.purger_loader'
        );
    }
}
