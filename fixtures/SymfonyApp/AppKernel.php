<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\SymfonyApp;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Hautelook\AliceBundle\HautelookAliceBundle;
use Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\Bundle\ABundle\TestABundle;
use Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\Bundle\BBundle\TestBBundle;
use Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\Bundle\CBundle\TestCBundle;
use Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\Bundle\DBundle\TestDBundle;
use Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\TestBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new FrameworkBundle(),
            new HautelookAliceBundle(),
            new TestBundle(),
            new TestABundle(),
            new TestBBundle(),
            new TestCBundle(),
            new TestDBundle(),
        ];

        if (class_exists('Doctrine\Bundle\DoctrineBundle\DoctrineBundle', true)) {
            $bundles[] = new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle();
        }

        if (class_exists('Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle', true)) {
            $bundles[] = new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
        }

        if (class_exists('Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle', true)) {
            $bundles[] = new \Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');

        if (class_exists('Doctrine\Bundle\DoctrineBundle\DoctrineBundle', true)) {
            $loader->load(__DIR__.'/config/config_doctrine_orm.yml');
        }

        if (class_exists('Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle', true)) {
            $loader->load(__DIR__.'/config/config_doctrine_odm.yml');
        }
    }
}
