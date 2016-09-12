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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @internal
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

        $loader = new XmlFileLoader($container, new FileLocator(self::SERVICES_DIR));
        $loader->load('loader.xml');

        $this->loadDoctrineORMConfig($processedConfiguration, $loader, $container);
    }

    private function loadDoctrineORMConfig(array $configs, LoaderInterface $loader)
    {
        /** @var bool|null $isEnabled */
        $isEnabled = $configs['db_drivers']['doctrine_orm'];
        if ($isEnabled || (null === $isEnabled && class_exists('Doctrine\Bundle\DoctrineBundle\DoctrineBundle'))) {
            $loader->load('doctrine_orm.xml');
        }
    }
}
