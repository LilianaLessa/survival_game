<?php

declare(strict_types=1);

namespace App\System\Screen;

use App\Engine\Component\DrawableInterface;
use App\Engine\Component\MapPosition;
use App\Engine\Entity\EntityCollection;
use App\Engine\Entity\EntityManager;
use App\System\World\WorldManager;
use App\System\World\WorldPresetLibrary;
use function Amp\async;
use function Amp\delay;

class ScreenUpdater
{
    private readonly int $fps;

    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly WorldManager  $world,
        WorldPresetLibrary $worldPresetLibrary,
    ) {
        $this->fps = $worldPresetLibrary->getDefaultWorldPreset()->getScreenUpdaterFps();
    }

    public function startAsyncUpdate(): void
    {
        async(function () {
            $frameDurationInSeconds = 1 / max(1, $this->fps);
            do {
                $this->updateScreen();
                $this->world->drawByBufferSwap();
                delay($frameDurationInSeconds);
            } while (1);
        });
    }

    private function updateScreen(): void
    {
        /** @var EntityCollection[][] $entityMap */
        $entityMap = [];
        $entitiesToUpdate = $this->entityManager->getEntitiesWithComponents(
            MapPosition::class,
            DrawableInterface::class,
        );

        /** @var MapPosition $position */
        foreach ($entitiesToUpdate as $entityId => [$position]) {
            $entityMap[$position->getX()][$position->getY()] =
                $entityMap[$position->getX()][$position->getY()] ?? new EntityCollection();

            $entityMap[$position->getX()][$position->getY()]->addEntity(
                $this->entityManager->getEntityById($entityId)
            );
        }

        $this->world->resetEntityMap();
        $this->world->setEntityMap($entityMap);
    }
}
