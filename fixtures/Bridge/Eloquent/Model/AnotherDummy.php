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

namespace Fidry\AliceDataFixtures\Bridge\Eloquent\Model;

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
        'dummy',
    ];

    /**
     * @inheritdoc
     */
    public $timestamps = false;

    public function dummy()
    {
        return $this->belongsTo(Dummy::class);
    }
}
