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

class AnotherDummy extends Model
{
    protected $table = 'another_dummies';

    protected $fillable = [
        'id',
        'address',
        'dummy',
    ];

    public $timestamps = false;

    public function dummy(): BelongsTo
    {
        return $this->belongsTo(Dummy::class);
    }
}
