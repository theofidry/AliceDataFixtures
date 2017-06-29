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

namespace Fidry\AliceDataFixtures\Loader;

use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\NotCallableTrait;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FakeLoader implements LoaderInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function load(array $fixturesFiles, array $parameters = [], array $objects = [], PurgeMode $purgeMode = null): array
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
