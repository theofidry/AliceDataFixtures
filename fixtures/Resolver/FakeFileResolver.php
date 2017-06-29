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

namespace Fidry\AliceDataFixtures\Resolver;

use Fidry\AliceDataFixtures\FileResolverInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FakeFileResolver implements FileResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(array $filePaths): array
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
