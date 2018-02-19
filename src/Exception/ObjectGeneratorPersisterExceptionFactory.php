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

namespace Fidry\AliceDataFixtures\Exception;

use LogicException;

/**
 * @private
 */
final class ObjectGeneratorPersisterExceptionFactory
{

    public static function createForEntityMissingAssignedIdForField($entity): LogicException
    {
        return new LogicException(sprintf('No ID found for the entity "%1$s". If this entity has an auto ID generator, ' .
                'this may be due to having it disabled because one instance of the entity had an ID assigned. ' .
                'Either remove this assigned ID to allow the auto ID generator to operate or generate and ID for ' .
                'all the "%1$s" entities.',
                get_class($entity)
            ));
    }
}
