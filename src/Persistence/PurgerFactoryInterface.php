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

interface PurgerFactoryInterface
{
    /**
     * Creates a new purger with the given purger mode. As the purger is stateful, it may be useful sometimes to create
     * a new purger with the same state as an existing one and just have control on the purge mode.
     *
     * @param PurgeMode            $mode
     * @param PurgerInterface|null $purger
     *
     * @return PurgerInterface
     */
    public function create(PurgeMode $mode, PurgerInterface $purger = null): PurgerInterface;
}
