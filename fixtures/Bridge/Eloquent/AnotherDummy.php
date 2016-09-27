<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures\Bridge\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class AnotherDummy extends Model
{
    /**
     * @inheritdoc
     */
    protected $table = 'another_dummies';

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'id',
        'address',
    ];

    /**
     * @inheritdoc
     */
    public $timestamps = false;
}
