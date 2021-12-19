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
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dummy extends Model
{
    protected $table = 'dummies';

    protected $fillable = [
        'id',
        'name',
    ];

    public $timestamps = false;

    public function anotherDummy(): BelongsTo
    {
        return $this->belongsTo(AnotherDummy::class);
    }

    public function setAnotherDummy(AnotherDummy $anotherDummy): void
    {
        if (null === $anotherDummy->id) {
            $anotherDummy->save();
        }
        $this->anotherDummy()->associate($anotherDummy);
    }
}
