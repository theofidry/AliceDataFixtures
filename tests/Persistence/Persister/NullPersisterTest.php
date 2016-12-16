<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Fidry\AliceDataFixtures\Persistence\Persister;

use Fidry\AliceDataFixtures\Persistence\PersisterInterface;

/**
 * @covers \Fidry\AliceDataFixtures\Persistence\Persister\NullPersister
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class NullPersisterTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAPersister()
    {
        $this->assertTrue(is_a(NullPersister::class, PersisterInterface::class, true));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsNotClonable()
    {
        clone new NullPersister();
    }
}
