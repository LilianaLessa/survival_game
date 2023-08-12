<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\DrawableInterface;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityCollection;
use App\Engine\Entity\EntityManager;
use App\System\World;

class MapDrawUpdater implements ProcessorSystemInterface
{
    public function __construct(private readonly World $world, private readonly EntityManager $entityManager)
    {
    }

    public function process(): void
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
