<?php

declare(strict_types=1);

namespace App\System\Event\Event;


use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\Entity;

class MapEntityUpdated extends AbstractEvent
{
    public const EVENT_NAME = 'map_entity.updated';
    public function __construct(
        private readonly Entity $entity,
    ) {
    }


    public function getEntity(): Entity
    {
        return $this->entity;
    }

    public function getEventName(): string
    {
       return self::EVENT_NAME;
    }

}
