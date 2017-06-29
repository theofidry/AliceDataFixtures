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

class User
{
    private $id;
    private $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function setGroups(array $groups)
    {
        foreach ($groups as $group) {
            $this->addGroup($group);
        }
    }

    public function addGroup(Group $group)
    {
        if (false === $this->groups->contains($group)) {
            $this->groups->add($group);
        }

        $group->addUser($this);
    }
}
