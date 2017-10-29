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

interface FileResolverInterface
{
    /**
     * Resolves a collection of file paths. For example may get the real path for each file, check their existence,
     * remove duplicates, sort files etc.
     *
     * @param string[] $filePaths
     *
     * @return string[] Resolved files
     */
    public function resolve(array $filePaths): array;
}
