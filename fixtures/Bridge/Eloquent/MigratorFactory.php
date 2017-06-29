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

namespace Fidry\AliceDataFixtures\Bridge\Eloquent;

use Fidry\AliceDataFixtures\Bridge\Eloquent\Migration\FakeMigrationRepository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class MigratorFactory
{
    public static function create(): Migrator
    {
        return new Migrator(
            new FakeMigrationRepository(),
            new FakeConnectionResolver(),
            new Filesystem()
        );
    }
}
