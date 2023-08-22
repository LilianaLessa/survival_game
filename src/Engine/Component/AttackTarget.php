<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Entity\Entity;

class AttackTarget implements ComponentInterface
{
    public function __construct(private readonly Entity $entityToAttack)
    {
    }

    public function getEntityToAttack(): Entity
    {
        return $this->entityToAttack;
    }
}
