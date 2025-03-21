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

use Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection\Compiler\RegisterTagServicesPass;
use Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection\Compiler\TaggedDefinitionsLocator;
use Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection\Configuration;
use Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection\FidryAliceDataFixturesExtension;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\InvalidKernel;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\NakedKernel;
use Fidry\AliceDataFixtures\Loader\MultiPassLoader;
use Fidry\AliceDataFixtures\Loader\SimpleLoader;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

#[CoversClass(FidryAliceDataFixturesBundle::class)]
#[CoversClass(Configuration::class)]
#[CoversClass(FidryAliceDataFixturesExtension::class)]
#[CoversClass(RegisterTagServicesPass::class)]
#[CoversClass(TaggedDefinitionsLocator::class)]
class FidryAliceDataFixturesBundleTest extends TestCase
{
    use ExpectDeprecationTrait;

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

    #[Group('legacy')]
    // TODO: remove this hack. This is purely for "Test code or tested code did not remove its own exception handlers".
    #[RunInSeparateProcess]
    public function testServiceRegistration(): void
    {
        self::assertServiceIsInstanceOf(
            MultiPassLoader::class,
            'fidry_alice_data_fixtures.loader.multipass_file'
        );

        self::assertServiceIsInstanceOf(
            SimpleLoader::class,
            'fidry_alice_data_fixtures.loader.simple'
        );
    }

    // TODO: remove this hack. This is purely for "Test code or tested code did not remove its own exception handlers".
    #[RunInSeparateProcess]
    public function testCannotBootIfNelmioAliceBundleIsNotRegistered(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot register "Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle" without "Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle".');

        $kernel = InvalidKernel::create();
        $kernel->boot();
        $kernel->shutdown();
    }

    final protected function assertServiceIsInstanceOf(string $serviceClass, string $serviceId): void
    {
        self::assertInstanceOf(
            $serviceClass,
            $this->kernel->getContainer()->get($serviceId)
        );
    }
}
