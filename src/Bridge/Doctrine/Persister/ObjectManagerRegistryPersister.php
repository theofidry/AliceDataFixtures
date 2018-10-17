<?php

declare(strict_types=1);

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Persister;

use Doctrine\Common\Persistence\ManagerRegistry;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;

/**
 * Class ObjectManagerRegistriesPersister.
 */
class ObjectManagerRegistryPersister implements PersisterInterface
{
    /**
     * @var ManagerRegistry[]
     */
    private $managerRegistries;

    /**
     * @var ObjectManagerPersister[]
     */
    private $managerPersisters;

    public function __construct(array $managerRegistries)
    {
        $this->managerRegistries = $managerRegistries;
    }

    /**
     * Persists objects into the database.
     *
     * @param object $object
     */
    public function persist($object)
    {
        foreach ($this->getManagerPersisters() as $persister) {
            $persister->persist($object);
        }
    }

    public function flush()
    {
        foreach ($this->getManagerPersisters() as $persister) {
            $persister->flush();
        }
    }

    private function getManagerPersisters()
    {
        if (null === $this->managerPersisters) {
            $this->managerPersisters = [];

            foreach ($this->managerRegistries as $managerRegistry) {
                foreach ($managerRegistry->getManagers() as $manager) {
                    $this->managerPersisters[] = new ObjectManagerPersister($manager);
                }
            }
        }

        return $this->managerPersisters;
    }
}
