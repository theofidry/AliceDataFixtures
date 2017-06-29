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

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Purger;

use Doctrine\Common\DataFixtures\Purger\MongoDBPurger as DoctrineMongoDBPurger;
use Doctrine\Common\DataFixtures\Purger\ORMPurger as DoctrineOrmPurger;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger as DoctrinePhpCrPurger;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface as DoctrinePurgerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager as DoctrineMongoDocumentManager;
use Doctrine\ODM\PHPCR\DocumentManager as DoctrinePhpCrDocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use Nelmio\Alice\IsAServiceTrait;

/**
 * Bridge for Doctrine purger.
 *
 * @author Vincent CHALAMON <vincentchalamon@gmail.com>
 * @final
 */
/* final */ class Purger implements PurgerInterface, PurgerFactoryInterface
{
    use IsAServiceTrait;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var DoctrinePurgerInterface
     */
    private $purger;

    public function __construct(ObjectManager $manager, PurgeMode $purgeMode = null)
    {
        $this->manager = $manager;

        $this->purger = static::createPurger($manager);
        if ($this->purger instanceof DoctrineOrmPurger && null !== $purgeMode) {
            $this->purger->setPurgeMode($purgeMode->getValue());
        }
    }

    /**
     * @inheritdoc
     */
    public function create(PurgeMode $mode, PurgerInterface $purger = null): PurgerInterface
    {
        if (null === $purger) {
            return new self($this->manager, $mode);
        }

        if ($purger instanceof DoctrinePurgerInterface) {
            $manager = $purger->getObjectManager();
        } elseif ($purger instanceof self) {
            $manager = $purger->manager;
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected purger to be either and instance of "%s" or "%s". Got "%s".',
                    DoctrinePurgerInterface::class,
                    __CLASS__
                )
            );
        }

        if (null === $manager) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected purger "%s" to have an object manager, got "null" instead.',
                    get_class($purger)
                )
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

    private static function createPurger(ObjectManager $manager): DoctrinePurgerInterface
    {
        if ($manager instanceof EntityManagerInterface) {
            return new DoctrineOrmPurger($manager);
        }

        if ($manager instanceof DoctrinePhpCrDocumentManager) {
            return new DoctrinePhpCrPurger($manager);
        }

        if ($manager instanceof DoctrineMongoDocumentManager) {
            return new DoctrineMongoDBPurger($manager);
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Cannot create a purger for ObjectManager of class %s',
                get_class($manager)
            )
        );
    }
}
