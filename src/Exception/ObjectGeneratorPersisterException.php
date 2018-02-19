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
final class ObjectGeneratorPersisterException extends LogicException
{
    /**
     * No ID found for the entity "EntityName". If this entity has an auto ID generator,
     * this may be due to having it disabled because one instance of the entity had an ID assigned.
     * Either remove this assigned ID to allow the auto ID generator to operate or generate and ID for
     * all the "EntityName" entities.
     * 
     * @param $entity
     * @return ObjectGeneratorPersisterException
     */
    public static function entityMissingAssignedIdForField($entity)
    {
        return new self("Entity of type " . get_class($entity) . " is missing an assigned ID. " .
            "The identifier generation strategy for this entity requires the ID field to be populated before ".
            "EntityManager#persist() is called. " .
            "" .
            "Please make sure that all defined objects in your fixture file for the entity of type " . get_class($entity) . " " .
            "have set a custom ID for the identifier. Mixing both is not possible with a post insert generator strategy."
        );
    }
}