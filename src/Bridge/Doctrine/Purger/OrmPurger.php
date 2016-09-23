<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Purger;

use Doctrine\Common\DataFixtures\Purger\ORMPurger as DoctrineOrmPurger;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface as DoctrinePurgerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;

/**
 * Bridge for Doctrine ORM purger.
 *
 * @author Vincent CHALAMON <vincentchalamon@gmail.com>
 */
final class OrmPurger implements PurgerInterface
{
    /**
     * @var DoctrinePurgerInterface
     */
    private $purger;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->purger = new DoctrineORMPurger($manager);
    }

    public static function fromPurger(DoctrineORMPurger $purger): self
    {
        if (null !== $manager = $purger->getObjectManager()) {
            throw new \InvalidArgumentException(
                'Expected purger "%s" to have an object manager, got "null" instead.',
                get_class($purger)
            );
        }

        $instance = new self($manager);
        $instance->purger = $purger;

        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function purge()
    {
        $this->purger->purge();
    }
}
