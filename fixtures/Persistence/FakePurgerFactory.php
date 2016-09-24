<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures\Persistence;

use Fidry\AliceDataFixtures\NotCallableTrait;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FakePurgerFactory implements PurgerFactoryInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function withDeletePurgeMode(PurgerInterface $purger): PurgerInterface
    {
        $this->__call(__METHOD__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function withTruncatePurgeMode(PurgerInterface $purger): PurgerInterface
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
