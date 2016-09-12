<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Executor;

use Doctrine\Common\Persistence\ObjectManager;
use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\ExecutorInterface;
use Nelmio\Alice\Persister\Doctrine;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
trait ExecutorTrait
{
    /**
     * @param ExecutorInterface $executor
     * @param ObjectManager     $manager
     * @param LoaderInterface   $loader
     * @param string[]          $fixtures Real path to fixtures files
     * @param bool              $append
     */
    private function executeExecutor(
        ExecutorInterface $executor,
        ObjectManager $manager,
        LoaderInterface $loader,
        array $fixtures,
        $append = false
    ) {
        $function = function (ObjectManager $manager) use ($executor, $loader, $fixtures, $append) {
            if (false === $append) {
                $executor->purge();
            }
            $loader->load(new Doctrine($manager), $fixtures);
        };

        if (method_exists($manager, 'transactional')) {
            $manager->transactional($function);
        } else {
            $function($manager);
        }
    }
}
