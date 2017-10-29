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

use Throwable;

/**
 * Utility class to keep track of the errors stacked while trying to load a given file.
 *
 * @private
 *
 * @deprecated
 */
final class ErrorTracker
{
    /**
     * @var array<string, Throwable>
     */
    private $stack = [];

    public function register(string $filePath, Throwable $error)
    {
        if (false === array_key_exists($filePath, $this->stack)) {
            $this->stack[$filePath] = [];
        }

        $this->stack[$filePath][] = $error;
    }

    /**
     * @return array<string, Throwable>
     */
    public function getStack(): array
    {
        return $this->stack;
    }
}
