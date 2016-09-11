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

use Doctrine\Common\Persistence\ObjectManager;
use Fidry\AlicePersistence\Alice\DataFixtures\LoaderInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface FixturesExecutorInterface
{
    /**
     * Executes the given array of data fixtures.
     *
     * @param ObjectManager   $manager
     * @param LoaderInterface $loader
     * @param string[]        $fixturesPath   Fixtures real paths
     * @param bool            $append         If true append the loaded data otherwise purge the database before
     * @param callable        $loggerCallable
     * @param bool            $truncate       The purge mode (truncate or delete).
     *
     * @return
     */
    public function execute(
        ObjectManager $manager,
        LoaderInterface $loader,
        array $fixturesPath,
        $append,
        $loggerCallable,
        $truncate = false
    );
}
