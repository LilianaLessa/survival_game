<?php

declare(strict_types=1);

namespace App\System\Event\Event;

use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractEvent extends Event
{
    abstract public function getEventName(): string;
}
