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

namespace Fidry\AliceDataFixtures\Bridge\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Webmozart\Assert\Assert;

class IdGenerator extends AbstractIdGenerator
{
    public const GENERATOR_TYPE_ALICE = 10;

    
    private AbstractIdGenerator $decorated;

    public function __construct(AbstractIdGenerator $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritDoc}
     */
    public function generate(EntityManager $em, $entity)
    {
        Assert::notNull($entity);

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
    public function isPostInsertGenerator(): bool
    {
        return $this->decorated->isPostInsertGenerator();
    }
}
