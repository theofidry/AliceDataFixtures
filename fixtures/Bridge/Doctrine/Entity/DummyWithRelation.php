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

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;

#[Entity]
class DummyWithRelation
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue]
    public int $id;

    #[OneToOne(targetEntity: 'DummyWithIdentifier', cascade: ['persist'])]
    #[JoinColumn(name: 'dummy_id', referencedColumnName: 'id')]
    public DummyWithIdentifier $dummy;
}
