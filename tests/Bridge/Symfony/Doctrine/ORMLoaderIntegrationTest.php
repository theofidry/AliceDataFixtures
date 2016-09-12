<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AlicePersistence\Bridge\Symfony\Doctrine;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Entity\Dummy;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\DoctrineOrmKernel;
use Fidry\AliceDataFixtures\LoaderInterface;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

/**
 * @group symfony
 * @group doctrine
 */
class ORMLoaderIntegrationTest extends TestCase
{
    public function testLoadAFile()
    {
        $kernel = new DoctrineOrmKernel('test', true);
        $kernel->boot();

        /** @var LoaderInterface $loader */
        $loader = $kernel->getContainer()->get('fidry_alice_data_fixtures.loader');
        $loader->load([__DIR__.'/../../../../fixtures/fixture_files/dummy.yml']);

        $doctrine = $kernel->getContainer()->get('doctrine');
        $result = $doctrine->getRepository(Dummy::class)->findAll();

        Assert::eq(1, count($result));

        $purger = new ORMPurger($doctrine);
        $purger->purge();

        $kernel->shutdown();
    }
}
