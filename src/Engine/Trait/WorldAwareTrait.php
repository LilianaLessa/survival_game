<?php

declare(strict_types=1);

namespace App\Engine\Trait;

use App\Engine\Component\Collideable;
use App\Engine\Component\TerrainHeight;
use App\System\World\WorldManager;

trait WorldAwareTrait
{
    private readonly WorldManager $world;


    private function canOverlapOnWorld(int $targetX, int $targetY): bool
    {
        $entitiesOnTarget = $this->world->getEntityCollection($targetX, $targetY);

        foreach ($entitiesOnTarget as $entityOnTarget) {
            if ($entityOnTarget->getComponent(Collideable::class)) {
                return false;
            }
        }

        return true;
    }

    private function getTerrainHeight(int $targetX, int $targetY): int
    {
        $entitiesOnTarget = $this->world->getEntityCollection($targetX, $targetY);

        foreach ($entitiesOnTarget as $entityOnTarget) {
            /** @var TerrainHeight $terrainHeight */
            $terrainHeight = $entityOnTarget->getComponent(TerrainHeight::class);
            if ($terrainHeight) {
                return $terrainHeight->getHeight();
            }
        }

        return 0;
    }
}
