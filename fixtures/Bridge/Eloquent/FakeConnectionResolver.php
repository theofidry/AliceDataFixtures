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

namespace Fidry\AliceDataFixtures\Bridge\Eloquent;

use Fidry\AliceDataFixtures\NotCallableTrait;
use Illuminate\Database\ConnectionResolverInterface;

class FakeConnectionResolver implements ConnectionResolverInterface
{
    use NotCallableTrait;

    public function connection($name = null): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getDefaultConnection(): string
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function setDefaultConnection($name): void
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
