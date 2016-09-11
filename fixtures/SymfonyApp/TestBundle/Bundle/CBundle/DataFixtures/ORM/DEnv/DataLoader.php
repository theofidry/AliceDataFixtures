<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\Bundle\CBundle\DataFixtures\ORM\DEnv;

use Hautelook\AliceBundle\Doctrine\DataFixtures\AbstractLoader;
use Symfony\Component\Finder\Finder;

class DataLoader extends AbstractLoader
{
    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        $finder = new Finder();

        $finder->in(__DIR__.'/products')->depth(0)->files()->name('*.yml')->sortByName();

        return iterator_to_array($finder);
    }
}
