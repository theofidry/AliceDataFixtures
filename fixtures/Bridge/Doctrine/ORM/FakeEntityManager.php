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

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Cache;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Fidry\AliceDataFixtures\NotCallableTrait;

class FakeEntityManager implements EntityManagerInterface
{
    use NotCallableTrait;

    public function getCache(): ?Cache
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getConnection(): Connection
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getExpressionBuilder(): Expr
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function beginTransaction(): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function transactional($func): mixed
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function commit(): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function rollback(): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function createQuery($dql = ''): Query
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function createNamedQuery($name): Query
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function createNativeQuery($sql, ResultSetMapping $rsm): NativeQuery
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function createNamedNativeQuery($name): NativeQuery
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function createQueryBuilder(): QueryBuilder
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getReference($entityName, $id)
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getPartialReference($entityName, $identifier)
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function close(): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function copy($entity, $deep = false)
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function lock($entity, $lockMode, $lockVersion = null): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getEventManager()
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getConfiguration()
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function isOpen()
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getUnitOfWork()
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getHydrator($hydrationMode)
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function newHydrator($hydrationMode)
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getProxyFactory()
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getFilters()
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function isFiltersStateClean()
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function hasFilters()
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function find($className, $id)
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function persist($object): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function remove($object): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function merge($object): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function clear($objectName = null): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function detach($object): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function refresh($object): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function flush(): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getRepository($className)
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getMetadataFactory()
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function initializeObject($obj): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function contains($object): bool
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getClassMetadata($className)
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
