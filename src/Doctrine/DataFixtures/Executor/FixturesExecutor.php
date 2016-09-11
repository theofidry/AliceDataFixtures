<?php

/*
 * This file is part of the Fidry\AlicePersistence package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AlicePersistence\Doctrine\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager as MongoDBDocumentManager;
use Doctrine\ODM\PHPCR\DocumentManager as PHPCRDocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Fidry\AlicePersistence\Alice\DataFixtures\LoaderInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class FixturesExecutor implements FixturesExecutorInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(
        ObjectManager $manager,
        LoaderInterface $loader,
        array $fixturesFiles,
        $append,
        $loggerCallable,
        $truncate = false
    ) {
        // Get executor
        $executor = $this->getExecutor($manager, $loader, $truncate);
        $executor->setLogger($loggerCallable);

        // Purge database and load fixtures
        $executor->execute($fixturesFiles, $append);
    }

    /**
     * Gets the executor for the matching the given object manager.
     *
     * @param ObjectManager   $manager
     * @param LoaderInterface $loader
     * @param bool|null       $purgeMode
     *
     * @return ExecutorInterface
     */
    private function getExecutor(ObjectManager $manager, LoaderInterface $loader, $purgeMode)
    {
        switch (true) {
            case $manager instanceof EntityManagerInterface:
                $executor = new ORMExecutor($manager, $loader);
                $purger = new ORMPurger($manager);
                $purger->setPurgeMode(
                    $purgeMode
                        ? ORMPurger::PURGE_MODE_TRUNCATE
                        : ORMPurger::PURGE_MODE_DELETE
                );
                break;

            default:
                throw new \InvalidArgumentException(sprintf(
                    'Unsupported manager type %s',
                    get_class($manager))
                );
        }

        $executor->setPurger($purger);

        return $executor;
    }
}
