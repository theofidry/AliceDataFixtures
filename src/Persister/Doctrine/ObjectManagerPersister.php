<?php

namespace Fidry\AlicePersistence\Persister\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Fidry\AlicePersistence\PersisterInterface;

final class ObjectManagerPersister implements PersisterInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var array Values are FQCN of persistable objects
     */
    private $persistableClasses;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->persistableClasses = array_flip($this->getPersistableClasses());
    }
    /**
     * @inheritDoc
     */
    public function persist(array $objects)
    {
        foreach ($objects as $object) {
            if (isset($this->persistableClasses[get_class($object)])) {
                $this->objectManager->persist($object);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        $this->objectManager->flush();
    }

    /**
     * @return string[]
     */
    private function getPersistableClasses(): array
    {
        $persistableClasses = [];

        $metadatas = $this->objectManager->getMetadataFactory()->getAllMetadata();
        foreach ($metadatas as $metadata) {
            if (isset($metadata->isEmbeddedClass) && $metadata->isEmbeddedClass) {
                continue;
            }

            $persistableClasses[] = $metadata->getName();
        }

        return $persistableClasses;
    }
}
