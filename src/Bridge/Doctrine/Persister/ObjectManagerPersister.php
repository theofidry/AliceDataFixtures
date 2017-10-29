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
}
