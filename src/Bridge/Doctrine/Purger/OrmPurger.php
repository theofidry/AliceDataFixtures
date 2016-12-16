<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Purger;

use Doctrine\Common\DataFixtures\Purger\ORMPurger as DoctrineOrmPurger;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface as DoctrinePurgerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use Nelmio\Alice\IsAServiceTrait;

/**
 * Bridge for Doctrine ORM purger.
 *
 * @author Vincent CHALAMON <vincentchalamon@gmail.com>
 *
 * @final
 */
/*final*/ class OrmPurger implements PurgerInterface, PurgerFactoryInterface
{
    use IsAServiceTrait;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var DoctrinePurgerInterface
     */
    private $purger;

    public function __construct(EntityManagerInterface $manager, PurgeMode $purgeMode)
    {
        $this->manager = $manager;
        $this->purger = new DoctrineOrmPurger($manager);
        $this->purger->setPurgeMode($purgeMode->getValue());
    }

    public function create(PurgeMode $mode, PurgerInterface $purger = null): PurgerInterface
    {
        if (null === $purger) {
            return new self($this->manager, $mode);
        }

        if ($purger instanceof DoctrineOrmPurger) {
            $manager = $purger->getObjectManager();
        } elseif ($purger instanceof self) {
            $manager = $purger->manager;
        } else {
            throw new \InvalidArgumentException(
                'Expected purger to be either and instance of "%s" or "%s". Got "%s".',
                DoctrineOrmPurger::class,
                __CLASS__
            );
        }

        if (null === $manager) {
            throw new \InvalidArgumentException(
                'Expected purger "%s" to have an object manager, got "null" instead.',
                get_class($purger)
            );
        }

        return new self($manager, $mode);
    }

    /**
     * @inheritdoc
     */
    public function purge()
    {
        $this->purger->purge();
    }
}
