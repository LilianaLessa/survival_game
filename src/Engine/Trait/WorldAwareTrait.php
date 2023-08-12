<?php

declare(strict_types=1);

namespace App\Engine\Trait;

use App\Engine\Component\Colideable;
use App\System\World;

trait WorldAwareTrait
{
    private readonly World $world;


    private function canOverlap(int $targetX, int $targetY): bool
    {
        $entitiesOnTarget = $this->world->getEntityCollection($targetX, $targetY);

        foreach ($entitiesOnTarget as $entityOnTarget) {
            if ($entityOnTarget->getComponent(Colideable::class)) {
                return false;
            }
        }

        return true;
    }
}
