<?php

declare(strict_types=1);

namespace App\System\Event\Event;

class UiMessageEvent extends AbstractEvent
{
    public const EVENT_NAME = 'ui.message';
    public function __construct(private readonly string $message)
    {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getEventName(): string
    {
       return self::EVENT_NAME;
    }
}
