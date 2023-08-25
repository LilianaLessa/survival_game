<?php

declare(strict_types=1);

namespace App\System\Event\Event;


use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\Entity;

class PlayerUpdated extends AbstractEvent
{
    public const EVENT_NAME = 'player.updated';
    public function __construct(
        private readonly PlayerCommandQueue $playerCommandQueue,
    ) {
    }

    public function getPlayerCommandQueue(): PlayerCommandQueue
    {
        return $this->playerCommandQueue;
    }

    public function getEventName(): string
    {
       return self::EVENT_NAME;
    }

}
