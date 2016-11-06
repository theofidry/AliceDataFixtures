<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Fidry\AliceDataFixtures\Persistence\Persister;

use Fidry\AliceDataFixtures\Persistence\PersisterInterface;

final class NullPersister implements PersisterInterface
{
    /**
     * @inheritdoc
     */
    public function persist($object)
    {
    }

    public function flush()
    {
    }
}
