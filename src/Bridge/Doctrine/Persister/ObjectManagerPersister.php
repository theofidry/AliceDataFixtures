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

use Doctrine\Common\Persistence\ManagerRegistry;
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

    /** @var ManagerRegistry $managerRegistry */
    private $managerRegistry;

    /**
     * @var ClassMetadata[] Entity metadata, FQCN being the key
     */
    private $metadata = [];

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @inheritdoc
     */
    public function persist($object)
    {
        $class = get_class($object);

        $manager = $this->managerRegistry->getManagerForClass($class);

        if ($manager) {
            $metadata = $this->getClassMetadata($manager, $class);

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
                $manager->persist($object);
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
        foreach ($this->managerRegistry->getManagers() as $manager) {
            $manager->flush();
        }
    }

    protected function configureIdGenerator(ORMClassMetadataInfo $metadata): void
    {
        $metadata->setIdGeneratorType(ORMClassMetadataInfo::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new ORMAssignedGenerator());
    }

    private function getClassMetadata(ObjectManager $manager, string $class): ClassMetadata
    {
        if (false === array_key_exists($class, $this->metadata)) {
            $this->metadata[$class] = $manager->getClassMetadata($class);
        }

        return $this->metadata[$class];
    }
}
