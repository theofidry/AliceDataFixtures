<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\Doctrine\Command;

use Doctrine\DBAL\Sharding\PoolingShardConnection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class DoctrineORMFixturesTest extends CommandTestCase
{
    /**
     * @var EntityManager
     */
    private $defaultEntityManager;

    protected function setUp()
    {
        if (false === class_exists('Doctrine\Bundle\DoctrineBundle\DoctrineBundle', true)) {
            $this->markTestSkipped('Bundle not installed.');
        }

        parent::setUp();

        $this->application->add(
            self::$kernel->getContainer()->get('hautelook_alice.doctrine.command.load_command')
        );

        $doctrineORM = $this->application->getKernel()->getContainer()->get('doctrine');
        $this->defaultEntityManager = $doctrineORM->getManager();

        // Create required MySQL databases for ORM
        $this->runConsole('doctrine:database:create', ['--if-not-exists' => true, '--connection' => 'mysql']);
        $this->runConsole('doctrine:database:create', ['--if-not-exists' => true, '--connection' => 'mysql', '--shard' => 1]);

        // Reset ORM schemas
        foreach ($doctrineORM->getManagers() as $name => $manager) {
            $this->runConsole('doctrine:schema:drop', ['--force' => true, '--em' => $name]);
            $this->runConsole('doctrine:schema:create', ['--em' => $name]);
            $connection = $manager->getConnection();
            if ($connection instanceof PoolingShardConnection) {
                $connection->connect(1);
                $this->runConsole('doctrine:schema:drop', ['--force' => true, '--em' => $name]);
                $this->runConsole('doctrine:schema:create', ['--em' => $name]);
                $connection->connect(0);
            }
        }
    }

    /**
     * @covers \Hautelook\AliceBundle\Doctrine\DataFixtures\AbstractLoader
     */
    public function testFixturesLoading()
    {
        $command = $this->application->find('hautelook_alice:doctrine:fixtures:load');

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => 'hautelook_alice:doctrine:fixtures:load'], ['interactive' => false]);

        $this->verifyProducts();
        $this->verifyBrands();
    }

    /**
     * @dataProvider loadCommandProvider
     *
     * @param array  $inputs
     * @param string $expected
     */
    public function testFixturesRegisteringUsingSQLite(array $inputs, $expected)
    {
        $command = $this->application->find('hautelook_alice:doctrine:fixtures:load');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array_merge([
            'command' => 'hautelook_alice:doctrine:fixtures:load',
        ], $inputs), ['interactive' => false]);

        $this->assertFixturesDisplayEquals($expected, $commandTester->getDisplay());
    }

    /**
     * @dataProvider loadCommandProvider
     *
     * @param array  $inputs
     * @param string $expected
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Doctrine ORM Manager named "foo" does not exist.
     */
    public function testFixturesRegisteringUsingInvalidManager(array $inputs, $expected)
    {
        $command = $this->application->find('hautelook_alice:doctrine:fixtures:load');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array_merge([
            'command' => 'hautelook_alice:doctrine:fixtures:load',
            '--manager' => 'foo',
        ], $inputs), ['interactive' => false]);

        $this->assertFixturesDisplayEquals($expected, $commandTester->getDisplay());
    }

    /**
     * @dataProvider loadCommandProvider
     *
     * @param array  $inputs
     * @param string $expected
     */
    public function testFixturesRegisteringUsingMySQL(array $inputs, $expected)
    {
        $command = $this->application->find('hautelook_alice:doctrine:fixtures:load');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array_merge([
            'command' => 'hautelook_alice:doctrine:fixtures:load',
            '--manager' => 'mysql',
        ], $inputs), ['interactive' => false]);

        $this->assertFixturesDisplayEquals($expected, $commandTester->getDisplay());
    }

    private function verifyProducts()
    {
        for ($i = 1; $i <= 10; ++$i) {
            /* @var \Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\Entity\Product */
            $product = $this->defaultEntityManager->find(
                'Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\Entity\Product',
                $i
            );
            $this->assertStringStartsWith('Awesome Product', $product->getDescription());

            // Make sure every product has a brand
            $this->assertInstanceOf(
                'Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\Entity\Brand',
                $product->getBrand()
            );
        }
    }

    private function verifyBrands()
    {
        for ($i = 1; $i <= 10; ++$i) {
            /* @var $brand \Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\Entity\Brand */
            $this->defaultEntityManager->find(
                'Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\Entity\Brand',
                $i
            );
        }
    }

    public function loadCommandProvider()
    {
        $data = [];

        $data[] = [
            [],
            <<<'EOF'
              > fixtures found:
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/brand.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/product.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/Bundle/ABundle/DataFixtures/ORM/aentity.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/Bundle/BBundle/DataFixtures/ORM/bentity.yml
  > purging database
  > fixtures loaded

EOF
        ];

        $data[] = [
            [
                '--env' => 'dev',
            ],
            <<<'EOF'
              > fixtures found:
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/brand.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/product.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/Dev/dev.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/Bundle/ABundle/DataFixtures/ORM/aentity.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/Bundle/BBundle/DataFixtures/ORM/bentity.yml
  > purging database
  > fixtures loaded

EOF
        ];

        $data[] = [
            [
                '--env' => 'Prod',
            ],
            <<<'EOF'
              > fixtures found:
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/brand.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/product.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/Prod/prod.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/Bundle/ABundle/DataFixtures/ORM/aentity.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/Bundle/BBundle/DataFixtures/ORM/bentity.yml
  > purging database
  > fixtures loaded

EOF
        ];

        $data[] = [
            [
                '--env' => 'prod',
            ],
            <<<'EOF'
              > fixtures found:
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/Bundle/ABundle/DataFixtures/ORM/aentity.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/Bundle/BBundle/DataFixtures/ORM/bentity.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/Prod/prod.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/brand.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/product.yml
  > purging database
  > fixtures loaded

EOF
        ];

        $data[] = [
            [
                '--env'    => 'dev',
                '--bundle' => [
                    'TestBundle',
                ],
            ],
            <<<'EOF'
              > fixtures found:
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/brand.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/product.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/Dev/dev.yml
  > purging database
  > fixtures loaded

EOF
        ];

        $data[] = [
            [
                '--env'    => 'dev',
                '--bundle' => [
                    'TestABundle',
                ],
            ],
            <<<'EOF'
              > fixtures found:
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/Bundle/ABundle/DataFixtures/ORM/aentity.yml
  > purging database
  > fixtures loaded

EOF
        ];

        $data[] = [
            [
                '--env'    => 'dev',
                '--bundle' => [
                    'TestBundle',
                    'TestABundle',
                ],
            ],
            <<<'EOF'
              > fixtures found:
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/brand.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/product.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/Dev/dev.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/Bundle/ABundle/DataFixtures/ORM/aentity.yml
  > purging database
  > fixtures loaded

EOF
        ];

        $data[] = [
            [
                '--env'    => 'dev',
                '--bundle' => [
                    'TestCBundle',
                ],
            ],
            <<<'EOF'
              > fixtures found:
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/Bundle/ABundle/DataFixtures/ORM/aentity.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/Bundle/BBundle/DataFixtures/ORM/bentity.yml
  > purging database
  > fixtures loaded

EOF
        ];

        $data[] = [
            [
                '--env'    => 'ignored',
                '--bundle' => [
                    'TestBundle',
                ],
            ],
            <<<'EOF'
              > fixtures found:
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/brand.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/product.yml
  > purging database
  > fixtures loaded

EOF
        ];

        $data[] = [
            [
                '--env'    => 'ignored2',
                '--bundle' => [
                    'TestBundle',
                ],
            ],
            <<<'EOF'
              > fixtures found:
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/brand.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/product.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/Ignored2/notIgnored.yml
  > purging database
  > fixtures loaded

EOF
        ];

        $data[] = [
            [
                '--env'    => 'provider',
                '--bundle' => [
                    'TestBundle',
                ],
            ],
            <<<'EOF'
              > fixtures found:
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/brand.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/product.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/Provider/testFormatter.yml
  > purging database
  > fixtures loaded

EOF
        ];

        $data[] = [
            [
                '--env'     => 'Shard',
                '--shard'   => 1,
                '--bundle'  => [
                    'TestBundle',
                ],
            ],
            <<<'EOF'
              > fixtures found:
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/brand.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/product.yml
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ORM/Shard/shard.yml
  > purging database
  > fixtures loaded

EOF
        ];

        // Fix paths
        foreach ($data as $index => $dataSet) {
            $data[$index][1] = str_replace('/home/travis/build/theofidry/AliceBundle', getcwd(), $dataSet[1]);
        }

        return $data;
    }
}
