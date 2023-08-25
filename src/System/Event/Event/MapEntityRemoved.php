<?php

declare(strict_types=1);

namespace App\System\Event\Event;

class MapEntityRemoved extends AbstractEvent
{
    public const EVENT_NAME = 'map_entity.removed';
    public function __construct(
        private readonly string $entityId,
    ) {
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function getEventName(): string
    {
        return self::EVENT_NAME;
    }
}
