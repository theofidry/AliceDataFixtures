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

use Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger;
use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundleTest as NakedFidryAliceDataFixturesBundleTest;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\DoctrineKernel;
use Fidry\AliceDataFixtures\Loader\PersisterLoader;
use Fidry\AliceDataFixtures\Loader\PurgerLoader;
use Override;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Symfony\Component\HttpKernel\KernelInterface;

#[CoversNothing]
class FidryAliceDataFixturesBundleTest extends NakedFidryAliceDataFixturesBundleTest
{
    protected KernelInterface $kernel;

    #[Override]
    public function setUp(): void
    {
        $this->kernel = DoctrineKernel::create();
        $this->kernel->boot();
    }

    #[Group('legacy')]
    // TODO: remove this hack. This is purely for "Test code or tested code did not remove its own exception handlers".
    #[RunInSeparateProcess]
    public function testServiceRegistration(): void
    {
        parent::testServiceRegistration();

        self::assertInstanceOf(
            Purger::class,
            $this->kernel->getContainer()->get('fidry_alice_data_fixtures.persistence.purger.doctrine.orm_purger')
        );

        self::assertInstanceOf(
            ObjectManagerPersister::class,
            $this->kernel->getContainer()->get('fidry_alice_data_fixtures.persistence.persister.doctrine.object_manager_persister')
        );

        self::assertInstanceOf(
            PersisterLoader::class,
            $this->kernel->getContainer()->get('fidry_alice_data_fixtures.doctrine.persister_loader')
        );

        self::assertInstanceOf(
            PurgerLoader::class,
            $this->kernel->getContainer()->get('fidry_alice_data_fixtures.doctrine.purger_loader')
        );
    }
}
