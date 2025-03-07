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

namespace Fidry\AliceDataFixtures\Bridge\Symfony\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JetBrains\PhpStorm\Pure;

class User
{
    private $id;

    /**
     * @var Collection<Group>
     */
    private readonly Collection $groups;

    #[Pure]
    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function setGroups(array $groups): void
    {
        foreach ($groups as $group) {
            $this->addGroup($group);
        }
    }

    public function addGroup(Group $group): void
    {
        if (false === $this->groups->contains($group)) {
            $this->groups->add($group);
        }

        $group->addUser($this);
    }
}
