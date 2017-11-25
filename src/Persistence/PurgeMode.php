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

namespace Fidry\AliceDataFixtures\Persistence;

use InvalidArgumentException;

final class PurgeMode
{
    private static $values = [
        'NO_PURGE_MODE' => 0,
        'DELETE_MODE' => 1,
        'TRUNCATE_MODE' => 2,
    ];

    /**
     * @var int
     */
    private $mode;

    public function __construct(int $mode)
    {
        if (false === array_key_exists($mode, array_flip(self::$values))) {
            throw new InvalidArgumentException(
                sprintf('Unknown purge mode "%d".', $mode)
            );
        }
        $this->mode = $mode;
    }

    public static function createDeleteMode(): self
    {
        return new self(self::$values['DELETE_MODE']);
    }

    public static function createTruncateMode(): self
    {
        return new self(self::$values['TRUNCATE_MODE']);
    }

    public static function createNoPurgeMode(): self
    {
        return new self(self::$values['NO_PURGE_MODE']);
    }

    public function getValue(): int
    {
        return $this->mode;
    }

    public function __toString(): string
    {
        return array_flip(self::$values)[$this->mode];
    }
}
