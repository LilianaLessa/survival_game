<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\World\World;

class SetMapViewport implements InvokableCommandInterface
{
    public function __construct(
        private readonly World $world,
        private readonly array $viewportSize
    ) {
    }


    public function __invoke()
    {
        $width = $this->world->getViewportWidth();
        $height = $this->world->getViewportHeight();

        $width = (int)($this->viewportSize[0] ?? $width);
        $height = (int)($this->viewportSize[1] ?? $height);

        $width = min(max(0, $width), $this->world->getWidth());
        $height = min(max(0, $height), $this->world->getHeight());

        $this->world->setViewportWidth($width);
        $this->world->setViewportHeight($height);

        Dispatcher::dispatch(
            new UiMessageEvent(
                sprintf(
                    "World viewport size set to %dx%d\n",
                    $width,
                    $height
                )
            )
        );
    }
}
