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
 * The ExtendedProcessorInterface is called before **any** of the objects and after **all** of them
 * are being persisted.
 *
 * @see \Fidry\AliceDataFixtures\Loader\PersisterLoader For an example of usage of processors
 */
interface ExtendedProcessorInterface
{
    /**
     * Allows to pre process all objects before any of them is persisted.
     *
     * @param object[] $objects An array where the key represents the fixture id and the value the object
     */
    public function preProcessAllObjects(array $objects): void;

    /**
     * Allows to pre process all objects before any of them is persisted.
     *
     * @param object[] $objects An array where the key represents the fixture id and the value the object
     */
    public function postProcessAllObjects(array $objects): void;
}
