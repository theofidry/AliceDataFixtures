<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once __DIR__.'/tests/Bridge/Doctrine/autoload.php';

return ConsoleRunner::createHelperSet($entityManager);
