<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\DriverManager;
use Doctrine\ODM\PHPCR\DocumentManagerInterface as PHPCRDocumentManager;
use Doctrine\ODM\PHPCR\Tools\Console\Helper\DocumentManagerHelper as PhpcrDocumentManagerHelperAlias;
use Doctrine\ORM\EntityManagerInterface as ORMEntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner as DoctrineORMConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Jackalope\Tools\Console\Command\InitDoctrineDbalCommand as JackalopeInitDbalCommand;
use Jackalope\Tools\Console\Helper\DoctrineDbalHelper as DoctrinePHPCRHelper;
use PHPCR\Util\Console\Helper\PhpcrConsoleDumperHelper;
use PHPCR\Util\Console\Helper\PhpcrHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Webmozart\Assert\Assert;

// See https://www.doctrine-project.org/projects/doctrine-orm/en/2.9/reference/configuration.html#setting-up-the-commandline-tool
// Depending on which Doctrine project we are using (ORM, ODM or PHP-CR) we
// set up the project differently.
//
// This is purely for the Doctrine commands that require this file as an entry
// point. The actual set-up (which is re-used for example running the tests)
// happens within the autoload files.

$isDoctrineORM = class_exists(DoctrineORMConsoleRunner::class);
$isDoctrinePHPCR = class_exists(JackalopeInitDbalCommand::class);
$isDoctrinePHPCRInitCommand = !isset($argv[1])
    || $argv[1] === 'jackalope:init:dbal'
    || $argv[1] === 'list'
    || $argv[1] === 'help';

if ($isDoctrineORM) {
    require_once __DIR__.'/tests/Bridge/Doctrine/autoload.php';

    /** @var ORMEntityManager $entityManager */
    $entityManager = $GLOBALS['entity_manager_factory']();
    Assert::isInstanceOf($entityManager, ORMEntityManager::class);

    return DoctrineORMConsoleRunner::run(
        new SingleManagerProvider($entityManager),
    );
}

if ($isDoctrinePHPCR) {
    if ($isDoctrinePHPCRInitCommand) {
        // We do not rely on the regular autoload file here. Indeed this file is
        // used for the Doctrine PHPCR initialization/management commands (i.e. not
        // for the tests) in which case we need a more minimalist approach as what
        // we are looking for is a connection to initialize the DB instead of having
        // a document manager which requires a session (which itself requires an
        // initialized environment).
        $connection = DriverManager::getConnection(
            require __DIR__.'/doctrine-phpcr-db-settings.php',
        );

        // For some reason phpcrodm uses `$extraCommands` if defined but does not
        // provide the init command by default hence we need to manually add it.
        $extraCommands = [new JackalopeInitDbalCommand()];

        // phpcrodm checks any global variable as possible HelperSet candidate
        $helperSet = new HelperSet([
            'connection' => new DoctrinePHPCRHelper($connection)
        ]);

        return;
    }

    require_once __DIR__.'/tests/Bridge/DoctrinePhpCr/autoload.php';

    /** @var PHPCRDocumentManager $documentManager */
    $documentManager = $GLOBALS['document_manager_factory']();
    $session = $documentManager->getPhpcrSession();

    $helperSet = new HelperSet([
        'phpcr' => new PhpcrHelper($session),
        'phpcr_console_dumper' => new PhpcrConsoleDumperHelper(),
        'dm' => new PhpcrDocumentManagerHelperAlias(null, $documentManager),
        'question' => new QuestionHelper(),
    ]);

    return;
}
