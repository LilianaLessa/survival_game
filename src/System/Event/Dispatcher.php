<?php

declare(strict_types=1);

namespace App\System\Event;

use Symfony\Component\EventDispatcher\EventDispatcher;

class Dispatcher
{
    private static ?self $instance = null;

    private EventDispatcher $dispatcher;

    public static function getInstance(): Dispatcher
    {
       return self::$instance = self::$instance ?? new self();
    }

    public function init()
    {
        $this->dispatcher = new EventDispatcher();
    }

    public function addListener(string $eventName, callable|array $listener, int $priority = 0)
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
    }

    public function dispatch(object $event, string $eventName = null): object
    {
        return $this->dispatcher->dispatch($event, $eventName);
    }

    private function __construct()
    {
        $this->init();
    }
}
