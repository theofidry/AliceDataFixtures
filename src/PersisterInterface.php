<?php

namespace Fidry\AlicePersistence;

interface PersisterInterface
{
    /**
     * Persists objects into the database.
     *
     * @param object[] $objects
     */
    public function persist(array $objects);

    public function flush();
}
