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
     * @var ClassMetadata[] Entity metadata, FQCN being the key
     */
    private $metadata = [];

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
            $metadata = $this->getMetadata($class);

            $generator = null;
            $generatorType = null;

            // Check if the ID is explicitly set by the user. To avoid the ID to be overridden by the ID generator
            // registered, we disable it for that specific object.
            if ($metadata instanceof ORMClassMetadataInfo) {
                if ($metadata->usesIdGenerator() && false === empty($metadata->getIdentifierValues($object))) {
                    $generator = $metadata->idGenerator;
                    $generatorType = $metadata->generatorType;

                    $this->configureIdGenerator($metadata);
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

            if (null !== $generator && false === $generator->isPostInsertGenerator()) {
                // Restore the generator if has been temporary unset
                $metadata->setIdGeneratorType($generatorType);
                $metadata->setIdGenerator($generator);
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

    protected function configureIdGenerator(ORMClassMetadataInfo $metadata): void
    {
        $metadata->setIdGeneratorType(ORMClassMetadataInfo::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new ORMAssignedGenerator());
    }

    private function getMetadata(string $class): ClassMetadata
    {
        if (false === array_key_exists($class, $this->metadata)) {
            $classMetadata = $this->objectManager->getClassMetadata($class);
            $this->metadata[$class] = $classMetadata;
        }

        return $this->metadata[$class];
    }
}
