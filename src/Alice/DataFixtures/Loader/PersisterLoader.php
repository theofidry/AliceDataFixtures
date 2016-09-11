<?php

/*
 * This file is part of the Fidry\AlicePersistence package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AlicePersistence\Alice\DataFixtures\Loader;

use Fidry\AlicePersistence\Alice\DataFixtures\LoaderInterface;
use Fidry\AlicePersistence\PersisterInterface;
use Fidry\AlicePersistence\ProcessorInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class PersisterLoader implements LoaderInterface
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var PersisterInterface
     */
    private $persister;

    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * @param LoaderInterface      $decoratedLoader
     * @param PersisterInterface   $persister
     * @param ProcessorInterface[] $processors
     */
    public function __construct(LoaderInterface $decoratedLoader, PersisterInterface $persister, array $processors)
    {
        $this->loader = $decoratedLoader;
        $this->persister = $persister;
        $this->processors = (function (ProcessorInterface ...$processors) { return $processors; })(...$processors);
    }

    /**
     * Pre process, persist and post process each object loaded.
     *
     * {@inheritdoc}
     */
    public function load(array $fixturesFiles, array $parameters = [], array $objects = []): array
    {
        $objects = $this->loader->load($fixturesFiles, $parameters, $objects);

        foreach ($objects as $object) {
            foreach ($this->processors as $processor) {
                $processor->preProcess($object);
            }

            $this->persister->persist($object);

            foreach ($this->processors as $processor) {
                $processor->postProcess($object);
            }
        }
        $this->persister->flush();

        return $objects;
    }
}
