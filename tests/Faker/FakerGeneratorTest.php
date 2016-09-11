<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\Faker;

use Faker\Generator;
use Hautelook\AliceBundle\Tests\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * Tests if the faker generator instance available as a service is properly configured.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FakerGeneratorTest extends KernelTestCase
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var Generator
     */
    private $faker;

    protected function setUp()
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
        $this->faker = $this->application->getKernel()->getContainer()->get('hautelook_alice.faker');
    }

    public function testGenerator()
    {
        $this->assertSame(
            'Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Faker\Provider\FooProvider',
            get_class($this->faker->getProviders()[0]),
            'Expected custom Faker provider to be registered.'
        );
    }
}
