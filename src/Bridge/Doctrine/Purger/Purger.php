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

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Purger;

use Doctrine\Persistence\ObjectManager;
use const E_USER_DEPRECATED;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use function trigger_error;

/**
 * @deprecated Use ObjectManagerPurger instead
 * @see ObjectManagerPurger
 */
/* final */ class Purger extends ObjectManagerPurger
{
    /**
     * @inheritdoc
     */
    public function __construct(ObjectManager $manager, PurgeMode $purgeMode = null)
    {
        @trigger_error(
            sprintf(
                '"%s" has been deprecated since v1.3.0. Use "%s" instead.',
                self::class,
                ObjectManagerPurger::class
            ),
            E_USER_DEPRECATED
        );

        parent::__construct($manager, $purgeMode);
    }
}
