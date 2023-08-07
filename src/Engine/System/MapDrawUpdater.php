<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Entity\Entity;
use App\System\World;

class MapDrawUpdater implements ProcessorSystemInterface
{
    public function __construct(private readonly World $world)
    {
    }

    /** @param Entity[] $entityCollection */
    public function process(array $entityCollection): void
    {
        $entityMap = [];

        foreach ($entityCollection as $entity) {
            /** @var MapSymbol $drawable */
            $drawable = $entity->getComponent(MapSymbol::class);
            /** @var MapPosition $position */
            $position = $entity->getComponent(MapPosition::class);
            if ($drawable && $position) {
                $entityMap[$position->getX()][$position->getY()] =
                    $entityMap[$position->getX()][$position->getY()] ?? [];
                $entityMap[$position->getX()][$position->getY()][] = $entity;
            }
        }

        $this->world->resetEntityMap();
        $this->world->setEntityMap($entityMap);
    }
}
