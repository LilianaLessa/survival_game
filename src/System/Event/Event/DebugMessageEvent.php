<?php

declare(strict_types=1);

namespace App\System\Event\Event;


use App\Engine\Component\PlayerCommandQueue;

class DebugMessageEvent extends AbstractEvent
{
    public const EVENT_NAME = 'debug.message';
    public function __construct(
        private readonly string $message,
        private readonly PlayerCommandQueue $playerCommandQueue
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getEventName(): string
    {
       return self::EVENT_NAME;
    }

    public function getPlayerCommandQueue(): PlayerCommandQueue
    {
        return $this->playerCommandQueue;
    }
}
