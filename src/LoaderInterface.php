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

namespace Fidry\AliceDataFixtures;

use Fidry\AliceDataFixtures\Persistence\PurgeMode;

interface LoaderInterface
{
    /**
     * Loads the fixtures files and return the loaded objects.
     *
     * @param string[]       $fixturesFiles Path to the fixtures files to loads.
     * @param array          $parameters
     * @param array          $objects
     * @param PurgeMode|null $purgeMode
     *
     * @return object[]
     */
    public function load(array $fixturesFiles, array $parameters = [], array $objects = [], PurgeMode $purgeMode = null): array;
}
