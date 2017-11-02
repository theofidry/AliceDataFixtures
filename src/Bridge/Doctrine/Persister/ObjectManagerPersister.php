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

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use Nelmio\Alice\IsAServiceTrait;

/**
 * @final
 */
/*final*/ class ObjectManagerPersister implements PersisterInterface
{
    use IsAServiceTrait;

    private $objectManager;

    /**
     * @var array|null Values are FQCN of persistable objects
     */
    private $persistableClasses;

    /**
     * @var ClassMetadataInfo[] Entity metadata, FQCN being the key
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

            if ($metadata->usesIdGenerator() && false === empty($metadata->getIdentifierValues($object))) {
                // user is trying to set an explicit identifier, but a ID generator is attached
                // override the generator
                $generator = $metadata->idGenerator;
                $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);
                $metadata->setIdGenerator(new AssignedGenerator());
            }

            $this->objectManager->persist($object);

            if (null !== $generator) {
                // reset the generator
                $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_CUSTOM);
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
            if (! $metadata->isMappedSuperclass && ! (isset($metadata->isEmbeddedClass) && $metadata->isEmbeddedClass)) {
                $persistableClasses[] = $metadata->getName();
            }
        }

        return $persistableClasses;
    }

    private function getMetadata(string $class): ClassMetadataInfo
    {
        if (false === \array_key_exists($class, $this->metadata)) {
            /** @var ClassMetadataInfo $classMetadata */
            $classMetadata = $this->objectManager->getClassMetadata($class);
            $this->metadata[$class] = $classMetadata;
        }
        return $this->metadata[$class];
    }
}
