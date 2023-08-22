<?php

declare(strict_types=1);

namespace App\System\Monster;

class MonsterDropPreset
{
    /** @var MonsterDropEvent[]  */
    private array $events;

    public function __construct(
        private readonly string $name,
        MonsterDropEvent ... $events
    )
    {
        $this->events = $events;
    }

    /** @return MonsterDropEvent[] */
    public function getEvents(): array
    {
        return $this->events;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
