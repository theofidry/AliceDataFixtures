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

namespace Fidry\AlicePersistence\Bridge\Symfony\Eloquent;

use Fidry\AliceDataFixtures\Bridge\Eloquent\Model\AnotherDummy;
use Fidry\AliceDataFixtures\Bridge\Eloquent\Model\Dummy;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\EloquentKernel;
use Fidry\AliceDataFixtures\LoaderInterface;
use Illuminate\Database\DatabaseManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @coversNothing
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ORMLoaderIntegrationTest extends TestCase
{
    /**
     * @var EloquentKernel
     */
    private $kernel;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @var int
     */
    private static $seed;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::$seed = uniqid();
    }

    public function setUp()
    {
        $this->kernel = new EloquentKernel(static::$seed, true);
        $this->kernel->boot();
        $this->kernel->getContainer()->get('wouterj_eloquent.initializer')->initialize();
        $this->kernel->getContainer()->get('wouterj_eloquent')->setAsGlobal();
        $this->databaseManager = $this->kernel->getContainer()->get('wouterj_eloquent.database_manager');

        $this->loader = $this->kernel->getContainer()->get('fidry_alice_data_fixtures.eloquent.persister_loader');
        $this->execute([
            'command' => 'eloquent:migrate',
            '--path' => 'migrations',
        ]);
    }

    public function tearDown()
    {
        $this->execute([
            'command' => 'eloquent:migrate:reset',
            '--path' => 'migrations',
        ]);
        $this->execute([
            'command' => 'eloquent:migrate',
            '--path' => 'migrations',
        ]);

        $this->kernel->shutdown();
        $this->kernel = null;
    }

    public function testLoadAFile()
    {
        $this->loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/eloquent_another_dummy.yml',
        ]);

        $this->assertEquals(1, AnotherDummy::all()->count());
    }

    public function testLoadAFileWithPurger()
    {
        AnotherDummy::create([
            'address' => 'hello',
        ]);

        $loader = $this->kernel->getContainer()->get('fidry_alice_data_fixtures.eloquent.purger_loader');
        $loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/eloquent_another_dummy.yml',
        ]);

        $this->assertEquals(1, AnotherDummy::all()->count());
    }

    public function testBidirectionalRelationships()
    {
        $this->loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/eloquent_relationship_dummies.yml',
        ]);

        $this->assertEquals(10, Dummy::all()->count());
        $this->assertEquals(10, AnotherDummy::all()->count());
    }

    public function testBidirectionalRelationshipsDeclaredInDifferentFiles()
    {
        $this->loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/eloquent_another_dummy.yml',
            __DIR__.'/../../../../fixtures/fixture_files/eloquent_dummies.yml',
        ]);

        $this->assertEquals(10, Dummy::all()->count());
        $this->assertEquals(1, AnotherDummy::all()->count());
    }

    private function execute(array $input)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput($input);
        $output = new BufferedOutput();
        $application->run($input, $output);
    }
}
