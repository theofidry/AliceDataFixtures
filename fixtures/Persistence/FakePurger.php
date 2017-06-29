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

use Fidry\AliceDataFixtures\NotCallableTrait;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FakePurger implements PurgerInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function purge()
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
