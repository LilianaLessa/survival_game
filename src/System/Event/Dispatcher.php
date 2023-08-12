<?php

declare(strict_types=1);

namespace App\System\Event;

use App\System\Event\Event\AbstractEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Dispatcher
{
    private static ?self $instance = null;

    private EventDispatcher $dispatcher;

    public static function getInstance(): Dispatcher
    {
       return self::$instance = self::$instance ?? new self();
    }

    public static function dispatch(AbstractEvent $event): void
    {
        self::getInstance()->dispatchEvent($event);
    }

    public function init()
    {
        $this->dispatcher = new EventDispatcher();
    }

    public function addListener(string $eventName, callable|array $listener, int $priority = 0)
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
    }

    private function dispatchEvent(AbstractEvent $event): void
    {
        $this->dispatcher->dispatch($event, $event->getEventName());
    }

    private function __construct()
    {
        $this->init();
    }
}
