<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures\Bridge\Eloquent\Model;

use Fidry\AliceDataFixtures\Bridge\Eloquent\Model\AnotherDummy;
use Illuminate\Database\Eloquent\Model;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class Dummy extends Model
{
    /**
     * @inheritdoc
     */
    protected $table = 'dummies';

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'id',
        'name',
    ];

    /**
     * @inheritdoc
     */
    public $timestamps = false;

    public function anotherDummy()
    {
        return $this->belongsTo(AnotherDummy::class);
    }
}
