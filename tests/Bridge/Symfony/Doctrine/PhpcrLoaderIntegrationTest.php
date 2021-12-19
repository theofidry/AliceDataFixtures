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

namespace Fidry\AlicePersistence\Bridge\Symfony\Doctrine;

use function bin2hex;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\Persistence\ManagerRegistry;
use Fidry\AliceDataFixtures\Bridge\Symfony\PhpCrDocument\Dummy;
use Fidry\AliceDataFixtures\Bridge\Symfony\SymfonyApp\DoctrinePhpcrKernel;
use Fidry\AliceDataFixtures\LoaderInterface;
use PHPUnit\Framework\TestCase;
use function random_bytes;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @coversNothing
 */
class PhpcrLoaderIntegrationTest extends TestCase
{
    private KernelInterface $kernel;

    private LoaderInterface $loader;

    private ManagerRegistry $doctrine;

    private static string $seed;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$seed = bin2hex(random_bytes(6));
    }

    public function setUp(): void
    {
        $this->kernel = new DoctrinePhpcrKernel(static::$seed, true);
        $this->kernel->boot();

        $this->loader = $this->kernel->getContainer()->get('fidry_alice_data_fixtures.doctrine_phpcr.persister_loader');
        $this->doctrine = $this->kernel->getContainer()->get('doctrine_phpcr');
    }

    public function tearDown(): void
    {
        $purger = new PHPCRPurger($this->doctrine->getManager());
        $purger->purge();

        $this->kernel->shutdown();
        unset($this->kernel);
    }

    public function testLoadAFile(): void
    {
        $this->loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/phpcr_dummy.yml',
        ]);

        $result = $this->doctrine->getRepository(Dummy::class)->findAll();

        self::assertCount(1, $result);
    }

    public function testLoadAFileWithPurger(): void
    {
        $dummy = new Dummy();
        $dummy->id = '/dummy_'.bin2hex(random_bytes(6));
        $dummyManager = $this->doctrine->getManager();
        $dummyManager->persist($dummy);
        $dummyManager->flush();
        $dummyManager->clear();

        $loader = $this->kernel->getContainer()->get('fidry_alice_data_fixtures.doctrine_phpcr.purger_loader');
        $loader->load([
            __DIR__.'/../../../../fixtures/fixture_files/phpcr_dummy.yml',
        ]);

        $result = $this->doctrine->getRepository(Dummy::class)->findAll();

        self::assertCount(1, $result);
    }
}
