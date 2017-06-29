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

require_once __DIR__.'/../../../vendor-bin/eloquent/vendor/autoload.php';

$manager = new Illuminate\Database\Capsule\Manager();
$manager->addConnection($config = [
    'driver' => false !== getenv('DB_DRIVER')? getenv('DB_DRIVER') : 'mysql',
    'username' => false !== getenv('DB_USER')? getenv('DB_USER') : 'root',
    'password' => false !== getenv('DB_PASSWORD')? getenv('DB_PASSWORD') : null,
    'database' => false !== getenv('DB_NAME')? getenv('DB_NAME') : 'fidry_alice_data_fixtures',
    'host' => 'localhost',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
    'strict' => true,
]);

$manager->bootEloquent();
$manager->setAsGlobal();

$resolver = new \Illuminate\Database\ConnectionResolver([
    'default' => $manager->getConnection(),
]);
$resolver->setDefaultConnection('default');

$repository = new \Illuminate\Database\Migrations\DatabaseMigrationRepository($resolver, 'migrations');
if (false === $repository->repositoryExists()) {
    $repository->createRepository();
}

$fileSystem = new \Illuminate\Filesystem\Filesystem();

$migrator = new \Illuminate\Database\Migrations\Migrator($repository, $resolver, $fileSystem);

$GLOBALS['manager'] = $manager;
$GLOBALS['resolver'] = $resolver;
$GLOBALS['repository'] = $repository;
$GLOBALS['file_system'] = $fileSystem;
$GLOBALS['migrator'] = $migrator;
