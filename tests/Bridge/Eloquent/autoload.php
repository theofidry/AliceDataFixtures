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

const ROOT = __DIR__.'/../../..';

require_once ROOT.'/vendor-bin/eloquent/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;

$managerRepositoryMigratorFactory = static function () {
    $manager = (static function (): Manager {
        $manager = new Manager();
        $manager->addConnection(require ROOT.'/eloquent-db-settings.php');
        $manager->bootEloquent();
        $manager->setAsGlobal();

        return $manager;
    })();

    $resolver = (static function (Manager $manager): ConnectionResolverInterface {
        $resolver = new ConnectionResolver([
            'default' => $manager->getConnection(),
        ]);
        $resolver->setDefaultConnection('default');

        return $resolver;
    })($manager);

    $repository = (static function (ConnectionResolverInterface $resolver): MigrationRepositoryInterface {
        $repository = new DatabaseMigrationRepository($resolver, 'migrations');

        if (false === $repository->repositoryExists()) {
            $repository->createRepository();
        }

        return $repository;
    })($resolver);

    $migrator = new Migrator($repository, $resolver, new Filesystem());

    return [$manager, $repository, $migrator];
};

$GLOBALS['manager_repository_migrator_factory'] = $managerRepositoryMigratorFactory;
