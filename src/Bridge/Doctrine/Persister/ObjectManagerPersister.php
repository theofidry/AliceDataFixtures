<?php

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Persister;

use Doctrine\Common\Persistence\ObjectManager;
use Fidry\AliceDataFixtures\PersisterInterface;

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
    public function persist($object)
    {
        if (isset($this->persistableClasses[get_class($object)])) {
            $this->objectManager->persist($object);
        }
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        $this->objectManager->flush();
        $this->objectManager->clear();
    }

    /**
     * @return string[]
     */
    private function getPersistableClasses(): array
    {
        $persistableClasses = [];

        $allMetadata = $this->objectManager->getMetadataFactory()->getAllMetadata();

        foreach ($allMetadata as $metadata) {
            if (! $metadata->isMappedSuperclass && ! (isset($metadata->isEmbeddedClass) && $metadata->isEmbeddedClass)) {
                $persistableClasses[] = $metadata->getName();
            }
        }

        return $persistableClasses;
    }
}
