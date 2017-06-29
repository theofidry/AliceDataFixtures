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

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class AnotherDummyFk extends Migration
{
    public function up()
    {
        Manager::schema()
            ->table(
                'another_dummies',
                function (Blueprint $table) {
                    $table->integer('dummy_id')->unsigned()->nullable();
                    $table->foreign('dummy_id', 'another_dummies_dummy_id_foreign')->references('id')->on('dummies');
                }
            )
        ;
    }

    public function down()
    {
        Manager::schema()
            ->table(
                'another_dummies',
                function (Blueprint $table) {
                    $table->dropForeign('another_dummies_dummy_id_foreign');
                }
            )
        ;
    }
}
