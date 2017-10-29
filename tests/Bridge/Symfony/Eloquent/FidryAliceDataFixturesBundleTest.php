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
    public function setUp()
    {
        $this->kernel = EloquentKernel::create();
        $this->kernel->boot();
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
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
