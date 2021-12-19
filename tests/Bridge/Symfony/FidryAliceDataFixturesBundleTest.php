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

namespace Fidry\AliceDataFixtures\Bridge\Symfony;

use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\InvalidKernel;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\NakedKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle
 * @covers \Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection\Configuration
 * @covers \Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection\FidryAliceDataFixturesExtension
 * @covers \Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection\Compiler\RegisterTagServicesPass
 * @covers \Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection\Compiler\TaggedDefinitionsLocator
 */
class FidryAliceDataFixturesBundleTest extends TestCase
{
    protected KernelInterface $kernel;

    public function setUp(): void
    {
        $this->kernel = NakedKernel::create();
        $this->kernel->boot();
    }

    public function tearDown(): void
    {
        $this->kernel->shutdown();
        (new Filesystem())->remove(__DIR__.'/../../../var/cache/');
    }

    /**
     * @group legacy
     * @expectedDepreaction The service "fidry_alice_data_fixtures.loader.multipass_file" is deprecated and will be removed in future versions.
     */
    public function testServiceRegistration(): void
    {
        $this->assertServiceIsInstanceOf(
            \Fidry\AliceDataFixtures\Loader\MultiPassLoader::class,
            'fidry_alice_data_fixtures.loader.multipass_file'
        );

        $this->assertServiceIsInstanceOf(
            \Fidry\AliceDataFixtures\Loader\SimpleLoader::class,
            'fidry_alice_data_fixtures.loader.simple'
        );
    }

    public function testCannotBootIfNelmioAliceBundleIsNotRegistered(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot register "Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle" without "Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle".');

        $kernel = InvalidKernel::create();
        $kernel->boot();
        $kernel->shutdown();
    }

    final protected function assertServiceIsInstanceOf(string $serviceClass, string $serviceId): void
    {
        $this->assertInstanceOf(
            $serviceClass,
            $this->kernel->getContainer()->get($serviceId)
        );
    }
}
