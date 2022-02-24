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

use function array_flip;
use function array_key_exists;
use function count;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations\File\Metadata;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as ODMClassMetadata;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata as PHPCRClassMetadata;
use Doctrine\ORM\EntityManagerInterface as ORMEntityManager;
use Doctrine\ORM\Id\AssignedGenerator as ORMAssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo as ORMClassMetadataInfo;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Fidry\AliceDataFixtures\Bridge\Doctrine\IdGenerator;
use Fidry\AliceDataFixtures\Exception\ObjectGeneratorPersisterExceptionFactory;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use function get_class;
use Nelmio\Alice\IsAServiceTrait;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use function spl_object_id;

class ObjectManagerPersister implements PersisterInterface
{
    use IsAServiceTrait;

    private ObjectManager $objectManager;

    /**
     * @var array Values are FQCN of persistable objects
     */
    private array $persistableClasses;

    /**
     * @var ClassMetadata[] Entity metadata to restore after flush, FQCN being the key.
     */
    private array $metadataToRestore = [];

    /**
     * @var array<string, true> Object IDs for which the metadata is checked –
     *                          necessary to prevent infinite loops with
     *                          bi-directional relationships
     */
    private array $objectChecked = [];

    private ReflectionProperty $unitOfWorkPersistersReflection;

    public function __construct(ObjectManager $manager)
    {
        $this->objectManager = $manager;
    }

    public function persist(object $object): void
    {
        if (!isset($this->persistableClasses)) {
            $this->persistableClasses = array_flip($this->getPersistableClasses($this->objectManager));
        }

        $className = get_class($object);

        if (isset($this->persistableClasses[$className])) {
            $metadata = $this->getMetadata($className, $object);

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

    public function flush(): void
    {
        $this->objectManager->flush();

        $metadataFactory = $this->objectManager->getMetadataFactory();

        if (null === $metadataFactory) {
            return;
        }

        foreach ($this->metadataToRestore as $metadata) {
            $metadataFactory->setMetadataFor($metadata->getName(), $metadata);
        }

        $this->metadataToRestore = [];
        $this->objectChecked = [];
    }

    private function getMetadata(string $className, object $object): ClassMetadata
    {
        $metadata = $this->objectManager->getClassMetadata($className);

        if (!($metadata instanceof ORMClassMetadataInfo)) {
            return $metadata;
        }

        $this->checkAssociationsMetadata($metadata, $object);

        // Check if the ID is explicitly set by the user. To avoid the ID to be overridden by the ID generator
        // registered, we disable it for that specific object.
        if (!$metadata->idGenerator instanceof IdGenerator
            && $metadata->usesIdGenerator()
            && 0 !== count($metadata->getIdentifierValues($object))
        ) {
            return $this->configureIdGenerator($metadata);
        }

        // Note: in the event we have the ODMClassMetadataInfo, we do nothing
        // as there is no equivalent to the ORM AssignedGenerator
        return $metadata;
    }

    private function checkAssociationsMetadata(ORMClassMetadataInfo $metadata, object $object): void
    {
        $objectId = spl_object_id($object);

        if (array_key_exists($objectId, $this->objectChecked)) {
            return;
        }

        $this->objectChecked[$objectId] = true;

        foreach ($metadata->getAssociationMappings() as $fieldName => $associationMapping) {
            if (!array_key_exists('targetEntity', $associationMapping)) {
                continue;
            }

            $targetEntityClassName = $associationMapping['targetEntity'];
            $fieldValueOrFieldValues = $metadata->getFieldValue($object, $fieldName);

            if (is_array($fieldValueOrFieldValues)) {
                foreach ($fieldValueOrFieldValues as $fieldValue) {
                    $this->getMetadata($targetEntityClassName, $fieldValue);
                }
            } elseif ($fieldValueOrFieldValues instanceof Collection) {
                foreach ($fieldValueOrFieldValues->getValues() as $fieldValue) {
                    $this->getMetadata($targetEntityClassName, $fieldValue);
                }
            } elseif ($fieldValueOrFieldValues !== null) {
                $this->getMetadata($targetEntityClassName, $fieldValueOrFieldValues);
            }
        }
    }

    /**
     * @return string[]
     */
    private function getPersistableClasses(ObjectManager $manager): array
    {
        $persistableClasses = [];
        $metadataFactory = $manager->getMetadataFactory();

        if (null === $metadataFactory) {
            return $persistableClasses;
        }

        $allMetadata = $metadataFactory->getAllMetadata();

        foreach ($allMetadata as $metadata) {
            if (self::isClassMetadataOfPersistableClass($metadata)) {
                $persistableClasses[] = $metadata->getName();
            }
        }

        return $persistableClasses;
    }

    private static function isClassMetadataOfPersistableClass(ClassMetadata $metadata): bool
    {
        $isMappedSuperClass = (
            $metadata instanceof ORMClassMetadataInfo
                || $metadata instanceof ODMClassMetadata
                || $metadata instanceof PHPCRClassMetadata
        )
            && $metadata->isMappedSuperclass;

        $isEmbeddedClass = $metadata instanceof ORMClassMetadataInfo
            && $metadata->isEmbeddedClass;

        return !($isMappedSuperClass || $isEmbeddedClass);
    }

    private function saveMetadataToRestore(ClassMetadata $metadata): void
    {
        $className = $metadata->getName();

        if (!isset($this->metadataToRestore[$className])) {
            $this->metadataToRestore[$metadata->getName()] = $metadata;
        }

        $this->clearUnitOfWorkPersister($metadata->getName());
    }

    private function configureIdGenerator(
        ORMClassMetadataInfo $metadata
    ): ORMClassMetadataInfo {
        $this->saveMetadataToRestore($metadata);

        $newMetadata = clone $metadata;
        $newMetadata->setIdGeneratorType(IdGenerator::GENERATOR_TYPE_ALICE);
        $newMetadata->setIdGenerator(new IdGenerator($metadata->idGenerator));

        $metadataFactory = $this->objectManager->getMetadataFactory();

        if (null === $metadataFactory) {
            return $metadata; // Do nothing
        }

        $className = $metadata->getName();

        $metadataFactory->setMetadataFor($className, $newMetadata);
        $this->clearUnitOfWorkPersister($className);

        return $newMetadata;
    }

    private function clearUnitOfWorkPersister(string $className): void
    {
        $objectManager = $this->objectManager;

        if (!($objectManager instanceof ORMEntityManager)) {
            return;
        }

        $unitOfWork = $objectManager->getUnitOfWork();

        try {
            $persistersReflection = $this->getUnitOfWorkPersistersReflection();
        } catch (ReflectionException $propertyNotFound) {
            // Do nothing: this will probably a case of a new UnitOfWork in
            // which case this hack should simply not apply
            return;
        }

        $persistersReflection->setAccessible(true);

        $persisters = $persistersReflection->getValue($unitOfWork);

        unset($persisters[$className]);

        $persistersReflection->setValue($unitOfWork, $persisters);
    }

    /**
     * @throws ReflectionException
     */
    private function getUnitOfWorkPersistersReflection(): ReflectionProperty
    {
        if (isset($this->unitOfWorkPersistersReflection)) {
            return $this->unitOfWorkPersistersReflection;
        }

        $unitOfWorkReflection = new ReflectionClass(UnitOfWork::class);

        $persistersReflection = $unitOfWorkReflection->getProperty('persisters');
        $persistersReflection->setAccessible(true);

        $this->unitOfWorkPersistersReflection = $persistersReflection;

        return $persistersReflection;
    }
}
