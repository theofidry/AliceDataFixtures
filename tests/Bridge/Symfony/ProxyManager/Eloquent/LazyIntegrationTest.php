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

use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundleTest as NakedFidryAliceDataFixturesBundleTest;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\EloquentKernelWithInvalidDatabase;

/**
 * @coversNothing
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class LazyIntegrationTest extends NakedFidryAliceDataFixturesBundleTest
{
    public function testTheApplicationCanBeStartedWithoutRequiringADatabaseConnection()
    {
        $kernel = EloquentKernelWithInvalidDatabase::create();
        $kernel->boot();
        $kernel->shutdown();
    }
}
