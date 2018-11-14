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

namespace Fidry\AliceDataFixtures\Processor;


use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Fidry\AliceDataFixtures\ExtendedProcessorInterface;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo as ODMClassMetadataInfo;
use Doctrine\ORM\Id\AssignedGenerator as ORMAssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo as ORMClassMetadataInfo;

class AutoDisableDoctrineIdGeneratorProcessor implements ExtendedProcessorInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ClassMetadata[] Entity metadata, FQCN being the key
     */
    private $metadata = [];

    /**
     * @var array
     */
    private $idGeneratorData = [];

    public function __construct(ObjectManager $manager)
    {
        $this->objectManager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function preProcessAllObjects(array $objects): void
    {
        foreach ($objects as $object) {
            $class = get_class($object);
            $metadata = $this->getMetadata($class);

            // Check if the ID is explicitly set by the user. To avoid the ID to be overridden by the ID generator
            // registered, we disable it for that specific object.
            if ($metadata instanceof ORMClassMetadataInfo) {
                if ($metadata->usesIdGenerator() && false === empty($metadata->getIdentifierValues($object))) {
                    $this->idGeneratorData[$class] = [
                        'generator' => $metadata->idGenerator,
                        'generatorType' => $metadata->generatorType,
                    ];

                    $this->configureIdGenerator($metadata);
                }
            } elseif ($metadata instanceof ODMClassMetadataInfo) {
                // Do nothing: currently not supported as Doctrine ODM does not have an equivalent of the ORM
                // AssignedGenerator.
            } else {
                // Do nothing: not supported.
            }
        }
    }

    private function getMetadata(string $class): ClassMetadata
    {
        if (false === array_key_exists($class, $this->metadata)) {
            $classMetadata = $this->objectManager->getClassMetadata($class);
            $this->metadata[$class] = $classMetadata;
        }
        return $this->metadata[$class];
    }

    protected function configureIdGenerator(ORMClassMetadataInfo $metadata): void
    {
        $metadata->setIdGeneratorType(ORMClassMetadataInfo::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new ORMAssignedGenerator());
    }

    /**
     * Allows to pre process all objects before any of them is persisted.
     *
     * @param object[] $objects An array where the key represents the fixture id and the value the object
     */
    public function postProcessAllObjects(array $objects): void
    {
        foreach ($objects as $object) {
            $class = get_class($object);

            if (!isset($this->idGeneratorData[$class])) {
                return;
            }

            $metadata = $this->getMetadata($class);

            $generator = $this->idGeneratorData[$class]['generator'];
            $generatorType = $this->idGeneratorData[$class]['generatorType'];

            if (null !== $generator && false === $generator->isPostInsertGenerator()) {
                // Restore the generator if has been temporary unset
                $metadata->setIdGeneratorType($generatorType);
                $metadata->setIdGenerator($generator);
            }
        }
    }
}
