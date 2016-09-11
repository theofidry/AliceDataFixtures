<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\DataFixtures\ORM;

use Hautelook\AliceBundle\Doctrine\DataFixtures\AbstractLoader;

abstract class AbstractDataLoader extends AbstractLoader
{
    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        \PHPUnit_Framework_Assert::assertInstanceOf(
            'Symfony\Component\DependencyInjection\ContainerInterface',
            $this->container
        );

        return [
            __DIR__.'/product.yml',
            __DIR__.'/brand.yml',
        ];
    }
}
