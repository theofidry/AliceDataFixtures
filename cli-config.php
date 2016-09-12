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

require_once __DIR__.'/tests/Bridge/Doctrine/autoload.php';

if (class_exists('Symfony\Component\Console\Helper\HelperSet')) {
    return ConsoleRunner::createHelperSet($entityManager);
}

return new \Symfony\Component\Console\Helper\HelperSet([
    'db' => new ConnectionHelper($em->getConnection()),
    'em' => new EntityManagerHelper($em)
]);
