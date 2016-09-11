<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\Alice\DataFixtures;

use Hautelook\AliceBundle\Alice\DataFixtures\Loader\SimpleLoader;
use Hautelook\AliceBundle\Tests\KernelTestCase;
use Nelmio\Alice\PersisterInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class LoaderFunctionalTest extends KernelTestCase
{
    /**
     * @var \Hautelook\AliceBundle\Alice\DataFixtures\Loader\SimpleLoader
     */
    private $loader;

    protected function setUp()
    {
        self::bootKernel();

        $this->loader = self::$kernel->getContainer()->get('hautelook_alice.fixtures.loader');
    }

    /**
     * @cover ::load
     * @cover ::persist
     */
    public function testLoadTrickyFixtures()
    {
        $files = [
            __DIR__.'/tricky_fixtures/strength.yml',
            __DIR__.'/tricky_fixtures/project.yml',
            __DIR__.'/tricky_fixtures/city.yml',
        ];

        $this->loader->load(new FakePersister(), $files);
    }

    /**
     * @expectedException \Hautelook\AliceBundle\Alice\DataFixtures\LoadingLimitException
     */
    public function testLoadFixtureWithNonexistentFixture()
    {
        $this->loader->load(new FakePersister(), [__DIR__.'/fixtures/nonexistent_fixture.yml']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown formatter "unknown"
     */
    public function testLoadFixtureWithNonexistentFunction()
    {
        $this->loader->load(new FakePersister(), [__DIR__.'/fixtures/nonexistent_function.yml']);
    }

    /**
     * @expectedException \Hautelook\AliceBundle\Alice\DataFixtures\LoadingLimitException
     */
    public function testLoadFixtureWithNonexistentMatchFixture()
    {
        $this->loader->load(new FakePersister(), [__DIR__.'/fixtures/nonexistent_match_fixture.yml']);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Parameter "unknown_param" was not found
     */
    public function testLoadFixtureWithNonexistentParameter()
    {
        $this->loader->load(new FakePersister(), [__DIR__.'/fixtures/nonexistent_parameter.yml']);
    }

    public function testLoadFixtureWithNonexistentVariable()
    {
        try {
            $this->loader->load(new FakePersister(), [__DIR__.'/fixtures/nonexistent_variable.yml']);
            $this->fails('Expected error to be thrown.');
        } catch (\Exception $exception) {
            $this->assertInstanceOf(\PHPUnit_Framework_Error_Notice::class, $exception);
            $this->assertEquals('Undefined variable: username', $exception->getMessage());
        }
    }

    /**
     * @expectedException \Hautelook\AliceBundle\Alice\DataFixtures\LoadingLimitException
     */
    public function testLoadFixtureWithPropertyOfNonexistentFixture()
    {
        $this->loader->load(new FakePersister(), [__DIR__.'/fixtures/property_of_nonexistent_fixture.yml']);
    }
}

class FakePersister implements PersisterInterface
{
    public $objects = [];

    public function persist(array $objects)
    {
    }

    public function find($class, $id)
    {
    }
}
