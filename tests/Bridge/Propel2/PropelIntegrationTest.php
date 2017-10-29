<?php

namespace Fidry\AliceDataFixtures\Bridge\Propel2;

use Nelmio\Alice\Loader\SimpleFilesLoader;
use PHPUnit\Framework\TestCase;
use Fidry\AliceDataFixtures\Bridge\Propel2\Persister\ModelPersister;
use Nelmio\Alice\Loader\SimpleFileLoader;
use Fidry\AliceDataFixtures\Loader\PersisterLoader;
use Fidry\AliceDataFixtures\Loader\SimpleLoader;
use Nelmio\Alice\Parser\Chainable\YamlParser;
use Symfony\Component\Yaml\Parser;
use Nelmio\Alice\Loader\NativeLoader;
use Fidry\AliceDataFixtures\Bridge\Propel2\PropelTestCase;
use Propel\Runtime\Propel;
use Fidry\AliceDataFixtures\Bridge\Propel2\Model\AuthorQuery;

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

