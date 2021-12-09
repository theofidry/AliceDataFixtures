<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Jackalope\Tools\Console\Command\InitDoctrineDbalCommand;
use Symfony\Component\Console\Helper\HelperSet;

// See https://www.doctrine-project.org/projects/doctrine-orm/en/2.9/reference/configuration.html#setting-up-the-commandline-tool
// Depending of which Doctrine project we are using (ORM, ODM or PHP-CR) we
// set-up the project differently.

$isDoctrineORM = class_exists(ConsoleRunner::class);
$isDoctrinePHPCR = class_exists(InitDoctrineDbalCommand::class);

function initializeDoctrineORM(EntityManagerInterface $entityManager): HelperSet {
    require_once __DIR__.'/tests/Bridge/Doctrine/autoload.php';

    return ConsoleRunner::createHelperSet($entityManager);
}

//function initializeDoctrinePHPCR() use ($entityManager): HelperSet {
//    require_once __DIR__.'/tests/Bridge/Doctrine/autoload.php';
//
//    return ConsoleRunner::createHelperSet($entityManager);
//}

if ($isDoctrineORM) {
    return initializeDoctrineORM($entityManager);
}

if ($isDoctrinePHPCR) {
    $extraCommands = [];
    $extraCommands[] = new InitDoctrineDbalCommand();

    if (isset($argv[1])
        && $argv[1] != 'jackalope:init:dbal'
        && $argv[1] != 'list'
        && $argv[1] != 'help'
    ) {
        require_once __DIR__.'/tests/Bridge/DoctrinePhpCr/autoload.php';

        $helperSet = new HelperSet(array(
            'phpcr' => new \PHPCR\Util\Console\Helper\PhpcrHelper($session),
            'phpcr_console_dumper' => new \PHPCR\Util\Console\Helper\PhpcrConsoleDumperHelper(),
            'dm' => new \Doctrine\ODM\PHPCR\Tools\Console\Helper\DocumentManagerHelper(null, $documentManager),
        ));

        $helperSet->set(new \Symfony\Component\Console\Helper\QuestionHelper(), 'question');
    } elseif (isset($argv[1]) && $argv[1] == 'jackalope:init:dbal') {
        $params = array(
            'driver' => false !== getenv('DB_DRIVER')? getenv('DB_DRIVER') : 'pdo_mysql',
            'user' => false !== getenv('DB_USER')? getenv('DB_USER') : 'root',
            'password' => false !== getenv('DB_PASSWORD')? getenv('DB_PASSWORD') : 'password',
            'dbname' => false !== getenv('DB_NAME')? getenv('DB_NAME') : 'fidry_alice_data_fixtures',
            'host' => false !== getenv('DB_HOST')? getenv('DB_HOST') : '127.0.0.1',
            'port' => false !== getenv('DB_PORT')? getenv('DB_PORT') : 3307,
        );

        $workspace = 'default';
        $user = 'admin';
        $pass = 'admin';

        $dbConn = \Doctrine\DBAL\DriverManager::getConnection($params);

        // special case: the init command needs the db connection, but a session is impossible if the db is not yet initialized
        $helperSet = new HelperSet(array(
            'connection' => new \Jackalope\Tools\Console\Helper\DoctrineDbalHelper($dbConn)
        ));
    }

    return $helperSet;
}
