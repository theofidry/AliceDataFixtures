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

namespace Fidry\AliceDataFixtures\File\Resolver;

use Fidry\AliceDataFixtures\FileResolverInterface;

class DummyResolver implements FileResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(array $filePaths): array
    {
        return $filePaths;
    }
}
