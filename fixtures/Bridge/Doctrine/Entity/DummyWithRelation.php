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

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Entity;

/**
 * @Entity
 */
class DummyWithRelation
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    public $id;

    /**
     * @var DummyWithIdentifier
     * @OneToOne(targetEntity="Dummy", cascade={"persist"})
     * @JoinColumn(name="dummy_id", referencedColumnName="id")
     */
    public $dummy;
}
