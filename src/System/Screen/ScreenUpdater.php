<?php

declare(strict_types=1);

namespace App\System\Screen;

use App\Engine\Component\DrawableInterface;
use App\Engine\Component\MapPosition;
use App\Engine\Entity\EntityCollection;
use App\Engine\Entity\EntityManager;
use App\System\World\World;
use function Amp\async;
use function Amp\delay;

class ScreenUpdater
{
    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly World $world,
        private readonly int $fps
    ) {
    }

    public function intiScreenUpdate(): void
    {
        async(function () {
            $frameDurationInSeconds = 1 / max(1, $this->fps);
            do {
                $this->updateScreen();
                system('clear');
                $this->world->draw();
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
