<?php

/*
 * This file is part of the Hautelook\AliceBundle package.
 *
 * (c) Baldur Rensch <brensch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hautelook\AliceBundle\Tests\Finder;

use Hautelook\AliceBundle\Resolver\BundlesResolver;

/**
 * @coversDefaultClass Hautelook\AliceBundle\Resolver\BundlesResolver
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class BundlesResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover ::resolveBundles
     */
    public function testResolveBundles()
    {
        $resolver = new BundlesResolver();

        $kernel = $this->prophesize('Symfony\Component\HttpKernel\KernelInterface');
        $kernel->getBundles()->willReturn(
            [
                'ABundle' => 'ABundleInstance',
                'BBundle' => 'BBundleInstance',
                'CBundle' => 'CBundleInstance',
            ]
        );
        $application = $this->prophesize('Symfony\Bundle\FrameworkBundle\Console\Application');
        $application->getKernel()->willReturn($kernel->reveal());

        $bundles = $resolver->resolveBundles($application->reveal(), ['ABundle']);
        $this->assertSame(['ABundle' => 'ABundleInstance'], $bundles);

        $bundles = $resolver->resolveBundles($application->reveal(), ['ABundle', 'BBundle']);
        $this->assertSame(['ABundle' => 'ABundleInstance', 'BBundle' => 'BBundleInstance'], $bundles);

        try {
            $resolver->resolveBundles($application->reveal(), ['UnknownBundle']);
            $this->fail('Expected exception to be thrown');
        } catch (\RuntimeException $exception) {
            // Expected result
        }

        try {
            $resolver->resolveBundles($application->reveal(), ['ABundle', 'UnknownBundle']);
            $this->fail('Expected exception to be thrown');
        } catch (\RuntimeException $exception) {
            // Expected result
        }
    }
}
