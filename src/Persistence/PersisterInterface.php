<?php

namespace Fidry\AliceDataFixtures\Persistence;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
interface PersisterInterface
{
    /**
     * Persists objects into the database.
     *
     * @param object $object
     */
    public function persist($object);

    public function flush();
}
