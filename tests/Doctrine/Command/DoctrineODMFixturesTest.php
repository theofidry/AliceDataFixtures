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

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class DoctrineODMFixturesTest extends CommandTestCase
{
    /**
     * @var DocumentManager
     */
    private $documentManager;

    protected function setUp()
    {
        if (false === class_exists('Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle', true)) {
            $this->markTestSkipped('Bundle not installed.');
        }
        
        parent::setUp();

        $this->application->add(
            self::$kernel->getContainer()->get('hautelook_alice.doctrine.mongodb.command.load_command')
        );

        $this->documentManager = $this->application->getKernel()->getContainer()->get('doctrine_mongodb')->getManager();
    }

    public function testFixturesLoading()
    {
        $command = $this->application->find('hautelook_alice:doctrine:mongodb:fixtures:load');

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => 'hautelook_alice:doctrine:mongodb:fixtures:load'], ['interactive' => false]);

        $this->verifyProducts();
    }

    /**
     * @dataProvider loadCommandProvider
     *
     * @param array  $inputs
     * @param string $expected
     */
    public function testFixturesRegistering(array $inputs, $expected)
    {
        $command = $this->application->find('hautelook_alice:doctrine:mongodb:fixtures:load');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array_merge(['command' => 'hautelook_alice:doctrine:mongodb:fixtures:load'], $inputs), ['interactive' => false]);

        $this->assertFixturesDisplayEquals($expected, $commandTester->getDisplay());
    }

    private function verifyProducts()
    {
        $products = $this->documentManager->getRepository('\Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\Document\Product')->findAll();

        $this->assertCount(10, $products);
    }

    public function loadCommandProvider()
    {
        $data = [];

        $data[] = [
            [],
            <<<'EOF'
              > fixtures found:
      - /home/travis/build/theofidry/AliceBundle/tests/SymfonyApp/TestBundle/DataFixtures/ODM/product.yml
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
