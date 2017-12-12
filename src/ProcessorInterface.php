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

namespace Fidry\AliceDataFixtures;

/**
 * Processor are meant to be used during the loading of files via a {@see LoaderInterface} in the scenario of having the
 * loaded objects persisted in the database.
 *
 * @see \Fidry\AliceDataFixtures\Loader\PersisterLoader For an example of usage of processors
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
interface ProcessorInterface
{
    /**
     * Processes an object before it is persisted to DB.
     *
     * @param string $id Fixture ID
     * @param object $object
     */
    public function preProcess(string $id, $object): void;

    /**
     * Processes an object after it is persisted to DB.
     *
     * @param string $id Fixture ID
     * @param object $object
     */
    public function postProcess(string $id, $object): void;
}
