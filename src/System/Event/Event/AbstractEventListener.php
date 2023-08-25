<?php

declare(strict_types=1);

namespace App\System\Event\Event;

use App\System\Event\Dispatcher;

abstract class AbstractEventListener
{
    public function __construct()
    {
        Dispatcher::getInstance()->addListener($this->getEventName(), $this);
    }

    abstract public function __invoke(AbstractEvent $event): void;

    abstract protected function getEventName(): string;
}
