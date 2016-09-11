<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests;

/**
 * Overrides the $class property as {@see SymfonyKernelTestCase::getKernelClass()} does not seems to resolve
 * properly the AppKernel class.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class KernelTestCase extends SymfonyKernelTestCase
{
    protected static $class = 'Hautelook\AliceBundle\Tests\SymfonyApp\AppKernel';
}
