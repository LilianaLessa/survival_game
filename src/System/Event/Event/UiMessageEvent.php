<?php

declare(strict_types=1);

namespace App\System\Event\Event;

use Symfony\Contracts\EventDispatcher\Event;

class UiMessageEvent extends Event
{
    public const EVENT_NAME = 'ui.message';
    public function __construct(private readonly string $message)
    {
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
