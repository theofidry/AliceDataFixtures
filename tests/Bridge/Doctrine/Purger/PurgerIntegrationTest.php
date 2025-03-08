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

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Purger;

use Doctrine\ORM\EntityManagerInterface;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\Dummy;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class PurgerIntegrationTest extends TestCase
{
    private EntityManagerInterface $manager;

    public function setUp(): void
    {
        $this->manager = $GLOBALS['entity_manager'];
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

        $purger = new Purger($this->manager, PurgeMode::createDeleteMode());
        $purger->purge();

        self::assertCount(0, $this->manager->getRepository(Dummy::class)->findAll());

        // Ensures the schema has been restored
        $dummy = new Dummy();
        $this->manager->persist($dummy);
        $this->manager->flush();
        self::assertCount(1, $this->manager->getRepository(Dummy::class)->findAll());
    }
}
