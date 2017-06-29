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

namespace Fidry\AliceDataFixtures\Alice\Loader;

use Fidry\AliceDataFixtures\NotCallableTrait;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\ObjectSet;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FakeFileLoader implements FileLoaderInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function loadFile(string $file, array $parameters = [], array $objects = []): ObjectSet
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
