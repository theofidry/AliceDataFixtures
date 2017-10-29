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

use InvalidArgumentException;

/**
 * Utility class to keep track of the files being loaded.
 *
 * @private
 *
 * @deprecated
 */
final class FileTracker
{
    private $files = [];

    public function __construct(string ...$files)
    {
        $this->files = array_fill_keys($files, false);
    }

    /**
     * @return string []
     */
    public function getUnloadedFiles(): array
    {
        return array_keys($this->files);
    }

    public function markAsLoaded(string $file)
    {
        if (false === array_key_exists($file, $this->files)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The file "%s" is not being tracked. As such, it cannot be marked as "loaded".',
                    $file
                )
            );
        }

        unset($this->files[$file]);
    }

    public function allFilesHaveBeenLoaded(): bool
    {
        return [] === $this->files;
    }
}
