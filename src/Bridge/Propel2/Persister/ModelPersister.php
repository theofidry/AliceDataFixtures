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

namespace Fidry\AliceDataFixtures\Bridge\Propel2\Persister;

use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use Nelmio\Alice\IsAServiceTrait;
use Propel\Runtime\Connection\ConnectionManagerSingle;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Propel;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 *
 * @final
 */
/*final*/ class ModelPersister implements PersisterInterface
{
    use IsAServiceTrait;

    /**
     * @var ConnectionManagerSingle
     */
    private $connectionManager;

    /**
     * @var Model[]
     */
    private $persistedModels = [];

    public function __construct()
    {
    }

    /**
     * @inheritdoc
     */
    public function persist($object)
    {
        if (false === $object instanceof ActiveRecordInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected object to be an instance of "%s", got "%s" instead.',
                    ActiveRecordInterface::class,
                    get_class($object)
                )
            );
        }

        $this->persistedModels[] = $object;
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        $models = $this->persistedModels;
        Propel::getConnection()->transaction(
            function () use ($models) {
                foreach ($models as $model) {
                    $model->save();
                }
            }
        );

        $this->persistedModels = [];
    }
}
