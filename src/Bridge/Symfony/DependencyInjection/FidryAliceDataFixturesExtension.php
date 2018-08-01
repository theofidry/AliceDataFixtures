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

namespace Fidry\AliceDataFixtures\Bridge\Symfony\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle;
use Doctrine\Bundle\PHPCRBundle\DoctrinePHPCRBundle;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger;
use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle;
use Fidry\AliceDataFixtures\Loader\PersisterLoader;
use Fidry\AliceDataFixtures\Loader\PurgerLoader;
use Fidry\AliceDataFixtures\ProcessorInterface;
use LogicException;
use Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use WouterJ\EloquentBundle\WouterJEloquentBundle;

/**
 * @private
 */
final class FidryAliceDataFixturesExtension extends Extension
{
    private const SERVICES_DIR = __DIR__.'/../Resources/config';

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processedConfiguration = $this->processConfiguration($configuration, $configs);

        $container->setParameter('fidry_alice_data_fixtures.default_purge_mode', $processedConfiguration['default_purge_mode']);

        $bundles = array_flip($container->getParameter('kernel.bundles'));

        if (false === array_key_exists(NelmioAliceBundle::class, $bundles)) {
            throw new LogicException(
                sprintf(
                    'Cannot register "%s" without "%s".',
                    FidryAliceDataFixturesBundle::class,
                    NelmioAliceBundle::class
                )
            );
        }

        $loader = new XmlFileLoader($container, new FileLocator(self::SERVICES_DIR));
        $loader->load('loader.xml');

        // Register autoconfiguration rules for Symfony DI 3.3+
        if (method_exists($container, 'registerForAutoconfiguration')) {
            $container->registerForAutoconfiguration(ProcessorInterface::class)
                ->addTag('fidry_alice_data_fixtures.processor');
        }

        $this->registerConfig(Configuration::DOCTRINE_ORM_DRIVER, DoctrineBundle::class, $bundles, $processedConfiguration, $loader);
        if ($processedConfiguration['db_drivers'][Configuration::DOCTRINE_ORM_DRIVER]) {
            $this->registerMultipleDoctrineObjectManagers($container);
        }
        $this->registerConfig(Configuration::DOCTRINE_MONGODB_ODM_DRIVER, DoctrineMongoDBBundle::class, $bundles, $processedConfiguration, $loader);
        $this->registerConfig(Configuration::DOCTRINE_PHPCR_ODM_DRIVER, DoctrinePHPCRBundle::class, $bundles, $processedConfiguration, $loader);
        $this->registerConfig(Configuration::ELOQUENT_ORM_DRIVER, WouterJEloquentBundle::class, $bundles, $processedConfiguration, $loader);
    }

    /**
     * Registers driver configuration.
     *
     * @param string $driver The driver name to register (doctrine_orm, eloquent_orm, ...).
     * @param string $bundle The bundle that should be checked for existence.
     * @param Bundle[] $bundles The bundles registered in current kernel.
     * @param array $configs The processed config array.
     * @param LoaderInterface $loader Config file loader
     */
    private function registerConfig(
        string $driver,
        string $bundle,
        array $bundles,
        array $configs,
        LoaderInterface $loader
    ) {
        /** @var bool|null $isEnabled */
        $isEnabled = $configs['db_drivers'][$driver];
        if (false === $isEnabled) {
            return;
        }

        $bundleIsRegistered = array_key_exists($bundle, $bundles);
        if ($isEnabled && false === $bundleIsRegistered) {
            throw new LogicException(
                sprintf(
                    'Cannot enable "%s" driver as the bundle "%s" is missing',
                    $driver,
                    $bundle
                )
            );
        }

        if ($bundleIsRegistered) {
            $loader->load($driver.'.xml');
        }
    }

    private function registerMultipleDoctrineObjectManagers(ContainerBuilder $container)
    {
        // var_dump($container->getParameterBag());
        $defaultEntityManagerName = $container->getParameterBag()->get('doctrine.default_entity_manager');
        $entityManagers = array_keys($container->getParameterBag()->get('doctrine.entity_managers'));

        foreach ($entityManagers as $entityManagerName) {
            $entityManagerRef = new Reference(sprintf('doctrine.orm.%s_entity_manager', $entityManagerName));
            $isDefaultEntityManager = $defaultEntityManagerName === $entityManagerName;

            $purgerFactoryServiceId = $this->registerPurgerFactory($container, $entityManagerName, $entityManagerRef);
            $objectManagerServiceId = $this->registerObjectManagerPersister($container, $entityManagerName, $entityManagerRef);
            $persisterLoaderServiceId = $this->registerPersisterLoader($container, $entityManagerName, $objectManagerServiceId);
            $purgerLoaderServiceId = $this->registerPurgerLoader($container, $entityManagerName, $purgerFactoryServiceId, $persisterLoaderServiceId);

            if ($isDefaultEntityManager) {
                // register default aliases
                $container->setAlias(
                    'fidry_alice_data_fixtures.persistence.doctrine.purger.purger_factory',
                    $purgerFactoryServiceId
                );
                $container->setAlias(
                    'fidry_alice_data_fixtures.persistence.persister.doctrine.object_manager_persister',
                    $objectManagerServiceId
                );
                $container->setAlias(
                    'fidry_alice_data_fixtures.doctrine.persister_loader',
                    $persisterLoaderServiceId
                );
                $container->setAlias(
                    'fidry_alice_data_fixtures.doctrine.purger_loader',
                    $purgerLoaderServiceId
                );
            }
        }
    }

    private function registerPurgerFactory(
        ContainerBuilder $container,
        string $entityManagerName,
        Reference $entityManagerRef
    ): string {
        $definition = new Definition(Purger::class, [ $entityManagerRef ]);
        $definition->setLazy(true);

        $purgerFactoryServiceId = sprintf(
            'fidry_alice_data_fixtures.persistence.doctrine.purger.%s_purger_factory',
            $entityManagerName
        );
        $container->setDefinition($purgerFactoryServiceId, $definition);

        return $purgerFactoryServiceId;
    }

    private function registerObjectManagerPersister(
        ContainerBuilder $container,
        string $entityManagerName,
        Reference $entityManagerRef
    ): string {
        $definition = new Definition(ObjectManagerPersister::class, [ $entityManagerRef ]);
        $definition->setLazy(true);

        $serviceId = sprintf(
            'fidry_alice_data_fixtures.persistence.persister.doctrine.%s_object_manager_persister',
            $entityManagerName
        );
        $container->setDefinition($serviceId, $definition);

        return $serviceId;
    }

    private function registerPersisterLoader(
        ContainerBuilder $container,
        string $entityManagerName,
        string $objectManagerServiceId
    ): string {
        $definition = new Definition(
            PersisterLoader::class,
            [
                new Reference('fidry_alice_data_fixtures.loader.simple'),
                new Reference($objectManagerServiceId),
                new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)
                // Processors are injected via a Compiler pass
            ]
        );
        $definition->setLazy(true);

        $serviceId = sprintf(
            'fidry_alice_data_fixtures.doctrine.%s_persister_loader',
            $entityManagerName
        );

        $container->setDefinition($serviceId, $definition);

        return $serviceId;
    }

    private function registerPurgerLoader(
        ContainerBuilder $container,
        string $entityManagerName,
        string $purgerFactoryServiceId,
        string $persisterLoaderServiceId
    ): string {
        $definition = new Definition(
            PurgerLoader::class,
            [
                new Reference($persisterLoaderServiceId),
                new Reference($purgerFactoryServiceId),
                '%fidry_alice_data_fixtures.default_purge_mode%',
                new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)
            ]
        );
        $definition->setLazy(true);

        $serviceId = sprintf(
            'fidry_alice_data_fixtures.doctrine.%s_purger_loader',
            $entityManagerName
        );

        $container->setDefinition($serviceId, $definition);

        return $serviceId;
    }
}
