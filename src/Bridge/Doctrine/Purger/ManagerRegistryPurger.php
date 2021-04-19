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

use function array_map;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use InvalidArgumentException;
use Nelmio\Alice\IsAServiceTrait;

/**
 * @final
 */
/* final */ class ManagerRegistryPurger implements PurgerInterface, PurgerFactoryInterface
{
    use IsAServiceTrait;

    private $registry;
    private $purgeMode;

    /**
     * @var PurgerInterface[]
     */
    private $purgers = [];

    public function __construct(ManagerRegistry $registry, PurgeMode $purgeMode = null)
    {
        $this->registry = $registry;

        $this->purgers = array_map(
            function (ObjectManager $manager) use ($purgeMode): PurgerInterface {
                return new ObjectManagerPurger($manager, $purgeMode);
            },
            $registry->getManagers()
        );
    }

    /**
     * @inheritdoc
     */
    public function create(PurgeMode $mode, PurgerInterface $purger = null): PurgerInterface
    {
        if (null !== $purger) {
            throw new InvalidArgumentException('Cannot create a new purger from an existing one.');
        }

        return new self($this->registry, $mode);
    }

    /**
     * @inheritdoc
     */
    public function purge(): void
    {
        foreach ($this->purgers as $purger) {
            $purger->purge();
        }
    }
}
