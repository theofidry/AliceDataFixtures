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

use function count;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;
use function is_array;
use function reset;
use Webmozart\Assert\Assert;

class IdGenerator extends AbstractIdGenerator
{
    public const int GENERATOR_TYPE_ALICE = 10;

    public function __construct(
        private readonly AbstractIdGenerator $decorated,
    ) {
    }

    public function generate(EntityManager $em, $entity): mixed
    {
        Assert::notNull($entity);

        $class = $entity::class;

        $metadata = $em->getClassMetadata($class);
        $idValues = $metadata->getIdentifierValues($entity);

        if (is_array($idValues) && count($idValues) == 1) {
            return reset($idValues);
        }

        return $this->decorated->generate($em, $entity);
    }

    public function isPostInsertGenerator(): bool
    {
        return $this->decorated->isPostInsertGenerator();
    }
}
