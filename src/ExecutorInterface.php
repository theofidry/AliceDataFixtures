<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface ExecutorInterface
{
    /**
     * Retrieve the ObjectManager instance this executor instance is using.
     *
     * @return ObjectManager
     */
    public function getObjectManager();

    /**
     * Purges the database before loading.
     */
    public function purge();

    /**
     * Executes the given array of data fixtures.
     *
     * @param object[] $fixtures Array of fixtures to execute.
     * @param bool     $append   Whether to append the data fixtures or purge the database before loading.
     */
    public function execute(array $fixtures, $append = false);
}
