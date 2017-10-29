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
namespace Fidry\AliceDataFixtures\Bridge\Propel2;

use Fidry\AliceDataFixtures\Bridge\Propel2\Model\AuthorQuery;
use Fidry\AliceDataFixtures\Bridge\Propel2\Persister\ModelPersister;
use Fidry\AliceDataFixtures\Loader\PersisterLoader;
use Fidry\AliceDataFixtures\Loader\SimpleLoader;
use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\Loader\SimpleFilesLoader;
use Nelmio\Alice\Parser\Chainable\YamlParser;
use Propel\Runtime\Propel;
use Symfony\Component\Yaml\Parser;

/**
 * @coversNothing
 */
class PropelIntegrationTest extends PropelTestCase
{
    /**
     * @var PersisterLoader
     */
    private $loader;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->initDatabase();

        $connection = Propel::getConnection('default');

        $modelPersister = new ModelPersister($connection);

        $this->loader = new PersisterLoader(
            new SimpleLoader(
                new SimpleFilesLoader(
                    new YamlParser(new Parser()),
                    new NativeLoader()
                )
            ),
            $modelPersister,
            []
        );
    }

    public function testLoad()
    {
        $this->loader->load([
            __DIR__.'/../../../fixtures/Bridge/Propel2/files/example1.yml'
        ]);

        $this->assertCount(5, AuthorQuery::create()->find());
    }
}
