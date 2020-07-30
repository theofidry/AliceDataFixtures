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

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo as ODMClassMetadataInfo;
use Doctrine\ORM\Id\AssignedGenerator as ORMAssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo as ORMClassMetadataInfo;
use Doctrine\ORM\ORMException;
use Fidry\AliceDataFixtures\Exception\ObjectGeneratorPersisterExceptionFactory;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use Nelmio\Alice\IsAServiceTrait;

class ObjectManagerPersister implements PersisterInterface
{
    use IsAServiceTrait;

    private $objectManager;

    /**
     * @var array|null Values are FQCN of persistable objects
     */
    private $persistableClasses;

    /**
     * @var ClassMetadata[] Entity metadata to restore after flush, FQCN being the key.
     */
    private $metadataToRestore = [];

    public function __construct(ObjectManager $manager)
    {
        $this->objectManager = $manager;
    }

    /**
     * @inheritdoc
     */
    public function persist($object)
    {
        if (null === $this->persistableClasses) {
            $this->persistableClasses = array_flip($this->getPersistableClasses($this->objectManager));
        }

        $class = get_class($object);

        if (isset($this->persistableClasses[$class])) {
            $metadata = $this->objectManager->getClassMetadata($class);

            // Check if the ID is explicitly set by the user. To avoid the ID to be overridden by the ID generator
            // registered, we disable it for that specific object.
            if ($metadata instanceof ORMClassMetadataInfo) {
                if ($metadata->usesIdGenerator() && false === empty($metadata->getIdentifierValues($object))) {
                    $metadata = $this->configureIdGenerator($metadata);
                }
            } elseif ($metadata instanceof ODMClassMetadataInfo) {
                // Do nothing: currently not supported as Doctrine ODM does not have an equivalent of the ORM
                // AssignedGenerator.
            } else {
                // Do nothing: not supported.
            }

            try {
                $this->objectManager->persist($object);
            } catch (ORMException $exception) {
                if ($metadata->idGenerator instanceof ORMAssignedGenerator) {
                    throw ObjectGeneratorPersisterExceptionFactory::createForEntityMissingAssignedIdForField($object);
                }

                throw $exception;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        $this->objectManager->flush();

        foreach ($this->metadataToRestore as $metadata) {
            $this->objectManager->getMetadataFactory()->setMetadataFor($metadata->getName(), $metadata);
        }
        $this->metadataToRestore = [];
    }

    /**
     * @return string[]
     */
    private function getPersistableClasses(ObjectManager $manager): array
    {
        $persistableClasses = [];
        $allMetadata = $manager->getMetadataFactory()->getAllMetadata();

        foreach ($allMetadata as $metadata) {
            /** @var ORMClassMetadataInfo|ODMClassMetadataInfo $metadata */
            if (false === $metadata->isMappedSuperclass
                && false === (isset($metadata->isEmbeddedClass) && $metadata->isEmbeddedClass)
            ) {
                $persistableClasses[] = $metadata->getName();
            }
        }

        return $persistableClasses;
    }

    private function saveMetadataToRestore(ClassMetadata $metadata): void
    {
        if (!isset($this->metadataToRestore[$metadata->getName()])) {
            $this->metadataToRestore[$metadata->getName()] = $metadata;
        }
    }

    private function configureIdGenerator(ORMClassMetadataInfo $metadata): ORMClassMetadataInfo
    {
        $this->saveMetadataToRestore($metadata);

        $newMetadata = clone $metadata;
        $newMetadata->setIdGeneratorType(ORMClassMetadataInfo::GENERATOR_TYPE_NONE);
        $newMetadata->setIdGenerator(new ORMAssignedGenerator());

        $this->objectManager->getMetadataFactory()->setMetadataFor($metadata->getName(), $newMetadata);

        return $newMetadata;
    }
}
