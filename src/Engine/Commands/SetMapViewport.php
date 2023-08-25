<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\Engine\Component\MapViewPort;
use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\EntityManager;
use App\System\Event\Dispatcher;
use App\System\Event\Event\PlayerUpdated;
use App\System\Event\Event\UiMessageEvent;
use App\System\World\WorldManager;

class SetMapViewport implements InvokableCommandInterface
{
    public function __construct(
        private readonly WorldManager  $worldManager,
        private readonly EntityManager $entityManager,
        private readonly string $entityId,
        private readonly array $viewportSize
    ) {
    }

    public function __invoke(PlayerCommandQueue $playerCommandQueue)
    {
        $width = $this->worldManager->getViewportWidth();
        $height = $this->worldManager->getViewportHeight();

        $width = (int)($this->viewportSize[0] ?? $width);
        $height = (int)($this->viewportSize[1] ?? $height);

        $width = min(max(0, $width), $this->worldManager->getWidth());
        $height = min(max(0, $height), $this->worldManager->getHeight());

//        $this->worldManager->setViewportWidth($width);
//        $this->worldManager->setViewportHeight($height);

        $uiMessage = sprintf(
            "Map viewport size set to %dx%d\n",
            $width,
            $height
        );

        $this->entityManager->updateEntityComponents(
            $this->entityId,
            new MapViewPort(
                $width,
                $height
            )
        );
        Dispatcher::getInstance()->dispatch(new UiMessageEvent($uiMessage, $playerCommandQueue));
    }
}
