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

namespace Fidry\AliceDataFixtures\Bridge\DoctrineMongoDB\Purger;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Fidry\AliceDataFixtures\Bridge\Doctrine\MongoDocument\Dummy;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @requires extension mongodb
 */
class PurgerIntegrationTest extends TestCase
{
    private DocumentManagerInterface $manager;

    protected function setUp(): void
    {
        $this->manager = $GLOBALS['document_manager_factory']();
    }

    protected function tearDown(): void
    {
        $this->manager->clear();
    }

    public function testEmptyDatabase(): void
    {
        $dummy = new Dummy();
        $this->manager->persist($dummy);
        $this->manager->flush();

        self::assertCount(1, $this->manager->getRepository(Dummy::class)->findAll());

        $purger = new Purger($this->manager);
        $purger->purge();

        self::assertCount(0, $this->manager->getRepository(Dummy::class)->findAll());

        // Ensures the schema has been restored
        $dummy = new Dummy();
        $this->manager->persist($dummy);
        $this->manager->flush();
        self::assertCount(1, $this->manager->getRepository(Dummy::class)->findAll());
    }
}
