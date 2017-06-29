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
class CreateDummiesTable extends Migration
{
    public function up()
    {
        Manager::schema()
            ->create(
                'dummies',
                function (Blueprint $table) {
                    $table->increments('id');
                    $table->string('name');
                    $table->integer('another_dummy_id')->unsigned();
                    $table->foreign('another_dummy_id')->references('id')->on('another_dummies');
                }
            )
        ;
    }

    public function down()
    {
        Manager::schema()->drop('dummies');
    }
}
