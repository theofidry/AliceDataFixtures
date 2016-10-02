<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection;

use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @private
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class FidryAliceDataFixturesExtension extends Extension
{
    const SERVICES_DIR = __DIR__.'/../Resources/config';

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processedConfiguration = $this->processConfiguration($configuration, $configs);
        $bundles = array_flip($container->getParameter('kernel.bundles'));

        if (false === array_key_exists('Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle', $bundles)) {
            throw new \LogicException(
                sprintf(
                    'Cannot register "%s" without "Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle".',
                    FidryAliceDataFixturesBundle::class
                )
            );
        }

        $loader = new XmlFileLoader($container, new FileLocator(self::SERVICES_DIR));
        $loader->load('loader.xml');

        $this->registerDoctrineORMConfig($bundles, $processedConfiguration, $loader);
        $this->registerEloquentConfig($bundles, $processedConfiguration, $loader);
    }

    private function registerDoctrineORMConfig(array $bundles, array $configs, LoaderInterface $loader)
    {
        /** @var bool|null $isEnabled */
        $isEnabled = $configs['db_drivers'][Configuration::DOCTRINE_ORM_DRIVER];
        if (false === $isEnabled) {
            return;
        }

        $doctrineBundleIsRegistered = array_key_exists('Doctrine\Bundle\DoctrineBundle\DoctrineBundle', $bundles);
        if ($isEnabled && false === $doctrineBundleIsRegistered) {
            throw new \LogicException(
                'Cannot enable doctrine driver as the bundle "Doctrine\Bundle\DoctrineBundle\DoctrineBundle" is missing'
            );
        }

        if ($doctrineBundleIsRegistered) {
            $loader->load('doctrine_orm.xml');
        }
    }

    private function registerEloquentConfig(array $bundles, array $configs, LoaderInterface $loader)
    {
        /** @var bool|null $isEnabled */
        $isEnabled = $configs['db_drivers'][Configuration::ELOQUENT_ORM_DRIVER];
        if (false === $isEnabled) {
            return;
        }

        $doctrineBundleIsRegistered = array_key_exists('WouterJ\EloquentBundle\WouterJEloquentBundle', $bundles);
        if ($isEnabled && false === $doctrineBundleIsRegistered) {
            throw new \LogicException(
                'Cannot enable doctrine driver as the bundle "WouterJ\EloquentBundle\WouterJEloquentBundle" is missing'
            );
        }

        if ($doctrineBundleIsRegistered) {
            $loader->load('eloquent_orm.xml');
        }
    }
}
