<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Fidry\AliceDataFixtures\Bridge\Symfony;

use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\InvalidKernel;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\NakedKernel;
use Fidry\AliceDataFixtures\Loader\MultiPassLoader;
use Fidry\AliceDataFixtures\Loader\SimpleLoader;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @covers \Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle
 * @covers \Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection\Configuration
 * @covers \Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection\FidryAliceDataFixturesExtension
 * @covers \Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection\Compiler\RegisterTagServicesPass
 * @covers \Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection\Compiler\TaggedDefinitionsLocator
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FidryAliceDataFixturesBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    public function setUp()
    {
        $this->kernel = new NakedKernel('InvalidKernel', true);
        $this->kernel->boot();
    }

    public function tearDown()
    {
        $this->kernel->shutdown();
    }

    public function testServiceRegistration()
    {
        $this->assertInstanceOf(
            MultiPassLoader::class,
            $this->kernel->getContainer()->get('fidry_alice_data_fixtures.loader.multipass_file')
        );

        $this->assertInstanceOf(
            SimpleLoader::class,
            $this->kernel->getContainer()->get('fidry_alice_data_fixtures.loader.simple_file')
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot register "Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle" without "Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle".
     */
    public function testCannotBootIfNelmioAliceBundleIsNotRegistered()
    {
        $kernel = new InvalidKernel('NewInvalidKernel', true);
        $kernel->boot();
        $kernel->shutdown();
    }
}
