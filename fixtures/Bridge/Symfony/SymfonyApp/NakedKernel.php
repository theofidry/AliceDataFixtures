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

namespace Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp;

use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle;
use JetBrains\PhpStorm\Pure;
use Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

class NakedKernel extends IsolatedKernel
{
    #[Pure]
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new NelmioAliceBundle(),
            new FidryAliceDataFixturesBundle(),
        ];
    }
}
