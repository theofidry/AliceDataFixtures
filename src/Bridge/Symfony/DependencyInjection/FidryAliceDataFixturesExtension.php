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
    /** @private */
    const SERVICES_DIR = __DIR__.'/../Resources/config';
    /** @private */
    const NELMIO_ALICE_BUNDLE_CLASS = 'Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle';
    /** @private */
    const DOCTRINE_ORM_BUNDLE_CLASS = 'Doctrine\Bundle\DoctrineBundle\DoctrineBundle';
    /** @private */
    const WOUTERJ_ELOQUENT_BUNDLE_CLASS = 'WouterJ\EloquentBundle\WouterJEloquentBundle';

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processedConfiguration = $this->processConfiguration($configuration, $configs);
        $bundles = array_flip($container->getParameter('kernel.bundles'));

        if (false === array_key_exists(self::NELMIO_ALICE_BUNDLE_CLASS, $bundles)) {
            throw new \LogicException(
                sprintf(
                    'Cannot register "%s" without "%s".',
                    FidryAliceDataFixturesBundle::class,
                    self::NELMIO_ALICE_BUNDLE_CLASS
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

        $doctrineBundleIsRegistered = array_key_exists(self::DOCTRINE_ORM_BUNDLE_CLASS, $bundles);
        if ($isEnabled && false === $doctrineBundleIsRegistered) {
            throw new \LogicException(
                sprintf(
                    'Cannot enable doctrine driver as the bundle "%s" is missing',
                    self::DOCTRINE_ORM_BUNDLE_CLASS
                )
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

        $doctrineBundleIsRegistered = array_key_exists(self::WOUTERJ_ELOQUENT_BUNDLE_CLASS, $bundles);
        if ($isEnabled && false === $doctrineBundleIsRegistered) {
            throw new \LogicException(
                sprintf(
                    'Cannot enable doctrine driver as the bundle "%s" is missing',
                    self::WOUTERJ_ELOQUENT_BUNDLE_CLASS
                )
            );
        }

        if ($doctrineBundleIsRegistered) {
            $loader->load('eloquent_orm.xml');
        }
    }
}
