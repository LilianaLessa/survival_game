<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\Fluid;
use App\Engine\Component\MapPosition;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\WorldAwareTrait;
use App\System\World;

class FluidDynamics implements PhysicsSystemInterface
{
    use WorldAwareTrait;

    public function __construct(private readonly World $world, private readonly EntityManager $entityManager)
    {
    }

    public function process(): void
    {
        $fluidEntitiesOnMap = $this->entityManager->getEntitiesWithComponents(
            Fluid::class,
            MapPosition::class
        );

        /** @var MapPosition $position */
        /** @var Fluid $fluid */
        foreach($fluidEntitiesOnMap as $entityId => [$position, $fluid]) {

            $vectorForce = $fluid->getForceDirection()->getVectorForce();
            $newPosition = [
                $position->getX() + $vectorForce['x'],
                $position->getY() + $vectorForce['y']
            ];

            $this->entityManager->updateEntityComponents($entityId, new MapPosition(...$newPosition));

            if ($this->world->isOutOfBounds($position->getX(), $position->getY())) {
                $this->entityManager->removeEntity($entityId);
            }
        }
    }
}
