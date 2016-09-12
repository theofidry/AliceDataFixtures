<?php

namespace Fidry\AliceDataFixtures;

interface ProcessorInterface
{
    /**
     * Processes an object before it is persisted to DB
     *
     * @param object $object instance to process
     */
    public function preProcess($object);

    /**
     * Processes an object after it is persisted to DB
     *
     * @param object $object instance to process
     */
    public function postProcess($object);
}
