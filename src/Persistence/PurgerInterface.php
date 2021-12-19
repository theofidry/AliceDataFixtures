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

/**
 * File copy/pasted from doctrine/data-fixtures to avoid a hard dependency on this package.
 *
 * @link https://github.com/doctrine/data-fixtures
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
interface PurgerInterface
{
    /**
     * Purges the database before loading. Depending of the implementation, the purge may truncate the database or
     * remove only a part of the database data.
     */
    public function purge(): void;
}
