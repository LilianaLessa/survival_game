<?php

declare(strict_types=1);

namespace App\System\Event\Event;


use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\Entity;

class UpdatePlayerCurrentTarget extends AbstractEvent
{
    public const EVENT_NAME = 'player.target_updated';
    public function __construct(
        private readonly PlayerCommandQueue $playerCommandQueue,
        private readonly Entity $currentTarget,
    ) {
    }

    public function getPlayerCommandQueue(): PlayerCommandQueue
    {
        return $this->playerCommandQueue;
    }

    public function getCurrentTarget(): Entity
    {
        return $this->currentTarget;
    }

    public function getEventName(): string
    {
       return self::EVENT_NAME;
    }
}
