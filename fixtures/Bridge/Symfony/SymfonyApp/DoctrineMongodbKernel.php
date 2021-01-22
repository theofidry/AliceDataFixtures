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

namespace Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp;

use Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle;
use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle;
use Fidry\PsyshBundle\PsyshBundle;
use Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;

class DoctrineMongodbKernel extends IsolatedKernel
{
    public function registerBundles()
    {
        $bundles = [
            new FrameworkBundle(),
            new NelmioAliceBundle(),
            new FidryAliceDataFixturesBundle(),
        ];

        if (class_exists(PsyshBundle::class)) {
            $bundles[] = new PsyshBundle();
        }

        if (class_exists(DoctrineMongoDBBundle::class)) {
            $bundles[] = new DoctrineMongoDBBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(__DIR__.'/config/config_doctrine_mongodb.yml');
    }
}
