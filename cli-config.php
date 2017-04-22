<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Jackalope\Tools\Console\Command\InitDoctrineDbalCommand;
use Symfony\Component\Console\Helper\HelperSet;

if (class_exists(ConsoleRunner::class)) {
    require_once __DIR__.'/tests/Bridge/Doctrine/autoload.php';

    if (class_exists(HelperSet::class)) {
        return ConsoleRunner::createHelperSet($entityManager);
    }

    return new \Symfony\Component\Console\Helper\HelperSet(
        [
            'db' => new ConnectionHelper($em->getConnection()),
            'em' => new EntityManagerHelper($em)
        ]
    );
} elseif (class_exists(InitDoctrineDbalCommand::class)) {
    $extraCommands = array();
    $extraCommands[] = new \Jackalope\Tools\Console\Command\InitDoctrineDbalCommand();

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
            'password' => false !== getenv('DB_PASSWORD')? getenv('DB_PASSWORD') : null,
            'dbname' => false !== getenv('DB_NAME')? getenv('DB_NAME') : 'fidry_alice_data_fixtures',
        );

        $workspace = 'default';
        $user = 'admin';
        $pass = 'admin';

        $dbConn = \Doctrine\DBAL\DriverManager::getConnection($params);

        // special case: the init command needs the db connection, but a session is impossible if the db is not yet initialized
        $helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
            'connection' => new \Jackalope\Tools\Console\Helper\DoctrineDbalHelper($dbConn)
        ));
    }

    return $helperSet;
}
