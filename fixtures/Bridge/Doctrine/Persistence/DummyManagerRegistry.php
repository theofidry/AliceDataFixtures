<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Persistence;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Fidry\AliceDataFixtures\NotCallableTrait;

class DummyManagerRegistry implements ManagerRegistry
{
    use NotCallableTrait;

    /**
     * @var ObjectManager
     */
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultConnectionName()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function getConnection($name = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function getConnections()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function getConnectionNames()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function getDefaultManagerName()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function getManager($name = null)
    {
        return $this->manager;
    }

    /**
     * @inheritdoc
     */
    public function getManagers()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function resetManager($name = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function getAliasNamespace($alias)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function getManagerNames()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function getRepository($persistentObject, $persistentManagerName = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function getManagerForClass($class)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }
}
