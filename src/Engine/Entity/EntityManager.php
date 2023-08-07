<?php

declare(strict_types=1);

namespace App\Engine\Entity;

class EntityManager
{
    /** @var Entity[] */
    private array $entities;

    public function __construct()
    {
        $this->entities = [];
    }

    public function getEntities(): array
    {
        return $this->entities;
    }

    public function addEntity(Entity $entity)
    {
        $this->entities[$entity->getId()] = $entity;
    }
}
