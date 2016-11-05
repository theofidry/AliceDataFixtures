<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Fidry\AliceDataFixtures\Persistence;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface PersisterAwareInterface
{
    /**
     * @param PersisterInterface $persister
     *
     * @return static
     */
    public function withPersister(PersisterInterface $persister);
}
