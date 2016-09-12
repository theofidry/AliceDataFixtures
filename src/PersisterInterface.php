<?php

namespace Fidry\AliceDataFixtures;

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
