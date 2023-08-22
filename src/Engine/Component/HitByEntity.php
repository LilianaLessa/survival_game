<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Entity\Entity;

class HitByEntity implements ComponentInterface
{
    public function __construct(private readonly Entity $entity)
    {
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }
}
