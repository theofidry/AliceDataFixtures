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

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FakeConnectionResolver implements ConnectionResolverInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function connection($name = null)
    {
        $this->__call(__METHOD__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function getDefaultConnection()
    {
        $this->__call(__METHOD__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function setDefaultConnection($name)
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
