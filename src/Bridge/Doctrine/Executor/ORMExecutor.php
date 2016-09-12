<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Executor;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor as DoctrineORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\ExecutorInterface;

/**
 * Class responsible for executing data fixtures.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class ORMExecutor extends DoctrineORMExecutor implements ExecutorInterface
{
    use ExecutorTrait;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @param EntityManagerInterface $manager EntityManagerInterface instance used for persistence.
     * @param LoaderInterface        $loader
     * @param ORMPurger              $purger
     */
    public function __construct(EntityManagerInterface $manager, LoaderInterface $loader, ORMPurger $purger = null)
    {
        parent::__construct($manager, $purger);

        $this->loader = $loader;
    }

    /**
     * @inheritdoc
     */
    public function purge()
    {
        $connection = $this->getObjectManager()->getConnection();
        $mysqlPlatform = (
            $this->purger->getPurgeMode() === ORMPurger::PURGE_MODE_TRUNCATE
            && $connection->getDatabasePlatform() instanceof MySqlPlatform
        );
        if ($mysqlPlatform) {
            $connection->exec('SET FOREIGN_KEY_CHECKS = 0;');
        }

        parent::purge();

        if ($mysqlPlatform) {
            $connection->exec('SET FOREIGN_KEY_CHECKS = 1;');
        }
    }

    /**
     * @inheritdoc
     */
    public function execute(array $fixtures, $append = false)
    {
        $this->executeExecutor($this, $this->getObjectManager(), $this->loader, $fixtures, $append);
    }
}
