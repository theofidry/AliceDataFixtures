<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures;

trait NotCallableTrait
{
    public function __call($method, $arguments)
    {
        throw new \DomainException(
            sprintf(
                'Did not expect "%s" to be called.',
                $method
            )
        );
    }
}
