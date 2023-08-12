<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\World;

class MapDrawUpdater implements ProcessorSystemInterface
{
    public function __construct(private readonly World $world, private readonly EntityManager $entityManager)
    {
    }

    public function process(): void
    {
        $entityMap = [];
        $entitiesToUpdate = $this->entityManager->getEntitiesWithComponents(
            MapSymbol::class,
            MapPosition::class,
        );

        /** @var MapPosition $position */
        foreach ($entitiesToUpdate as $entityId => [,$position]) {
            $entityMap[$position->getX()][$position->getY()] =
                $entityMap[$position->getX()][$position->getY()] ?? [];
            $entityMap[$position->getX()][$position->getY()][] = $this->entityManager->getEntityById($entityId);
        }

        $this->world->resetEntityMap();
        $this->world->setEntityMap($entityMap);
    }
}
