<?php

namespace Fidry\AliceDataFixtures\Bridge\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;

class IdGenerator extends AbstractIdGenerator
{
    const GENERATOR_TYPE_ALICE = 10;
    /**
     * @var AbstractIdGenerator
     */
    private $decorated;

    public function __construct(AbstractIdGenerator $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritDoc}
     */
    public function generate(EntityManager $em, $entity)
    {
        $class = get_class($entity);

        $metadata = $em->getClassMetadata($class);
        $idValues = $metadata->getIdentifierValues($entity);

        if (is_array($idValues) && count($idValues) == 1) {
            return reset($idValues);
        }

        return $this->decorated->generate($em, $entity);
    }

    /**
     * {@inheritDoc}
     */
    public function isPostInsertGenerator()
    {
        return $this->decorated->isPostInsertGenerator();
    }
}
