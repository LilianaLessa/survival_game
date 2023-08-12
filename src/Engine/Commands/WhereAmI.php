<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\Engine\Component\MapPosition;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;

class WhereAmI implements InvokableCommandInterface
{
    public function __construct(
        private readonly MapPosition $position
    ) {
    }


    public function __invoke()
    {
        Dispatcher::dispatch(
            new UiMessageEvent(
                sprintf(
                    "Current position: %d,%d\n",
                    $this->position->getX(),
                    $this->position->getY())
            )
        );
    }
}
