<?php

declare(strict_types=1);

namespace Fidry\AliceDataFixtures\Bridge\Doctrine\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

/**
 * @Entity
 */
class DummyWithProperty
{
    /**
     * @Id
     *
     * @Column(type="integer")
     *
     * @GeneratedValue
     */
    public int $id;

    /**
     * @Column(type="string", name="property", nullable=true)
     */
    public ?string $property = null;
}
