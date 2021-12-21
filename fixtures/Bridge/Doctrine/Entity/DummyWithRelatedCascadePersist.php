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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;

/**
 * @Entity
 */
class DummyWithRelatedCascadePersist
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    public ?int $id = null;

    /**
     * @ManyToOne(targetEntity="Dummy", cascade={"persist"})
     */
    public Dummy $related;

    /**
     * @ManyToOne(targetEntity="Dummy", cascade={"persist"})
     */
    public ?Dummy $relatedNullable = null;

    /**
     * @var Collection<AnotherDummy>
     * @ManyToMany(targetEntity=AnotherDummy::class, cascade={"persist"})
     * @JoinTable(
     *     name="dummmy_with_related_cascade_persist_to_another_dummy",
     *     joinColumns={
     *         @JoinColumn(name="user_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @JoinColumn(name="group_id", referencedColumnName="id")
     *     }
     * )
     */
    public Collection $relatedMultiple;

    public function __construct()
    {
        $this->relatedMultiple = new ArrayCollection();
    }
}
