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

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Persister;

use Doctrine\Persistence\ManagerRegistry;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use function get_class;
use function implode;
use InvalidArgumentException;
use Nelmio\Alice\IsAServiceTrait;
use function sprintf;

/**
 * @final
 */
class ManagerRegistryPersister implements PersisterInterface
{
    use IsAServiceTrait;

    private $registry;

    /**
     * @var PersisterInterface[]
     */
    private $persisters = [];

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;

        $managers = $registry->getManagers();

        foreach ($managers as $manager) {
            $this->persisters[spl_object_hash($manager)] = new ObjectManagerPersister($manager);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function persist($object)
    {
        $persister = $this->getPersisterForClass(get_class($object));

        $persister->persist($object);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        foreach ($this->persisters as $persister) {
            $persister->flush();
        }
    }

    private function getPersisterForClass(string $class): PersisterInterface
    {
        $manager = $this->registry->getManagerForClass($class);

        if (null === $manager) {
            throw new InvalidArgumentException(
                sprintf(
                    'Could not find a manager for the class "%s". Known managers: "%s"',
                    $class,
                    implode('", "', $this->registry->getManagerNames())
                )
            );
        }

        return $this->persisters[spl_object_hash($manager)];
    }
}
