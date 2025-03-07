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

class Group
{
    private $id;

    /**
     * @var Collection<User>
     */
    private Collection $users;

    #[Pure]
    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function setUsers(array $users): void
    {
        foreach ($users as $user) {
            $this->addUser($user);
        }
    }

    public function addUser(User $user): void
    {
        if (false === $this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }
}
