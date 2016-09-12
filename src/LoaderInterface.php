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

/**
 * The loader is class responsible for loading the fixtures files into objects and persist them into the database.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Loads the fixtures files.
     *
     * @param string[] $fixturesFiles Path to the fixtures files to loads.
     * @param array    $parameters
     * @param array    $objects
     *
     * @return array|\object[] Persisted objects
     */
    public function load(array $fixturesFiles, array $parameters = [], array $objects = []): array;
}
