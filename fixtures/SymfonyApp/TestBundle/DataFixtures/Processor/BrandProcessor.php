<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\DataFixtures\Processor;

use Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\Entity\Brand;
use Nelmio\Alice\ProcessorInterface;

/**
 * @link   https://github.com/nelmio/alice/blob/master/doc/processors.md#processors
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class BrandProcessor implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function preProcess($object)
    {
        if ($object instanceof Brand) {
            $object->canonicalName = strtolower($object->getName());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postProcess($object)
    {
    }
}
