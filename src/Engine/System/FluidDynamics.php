<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\Collideable;
use App\Engine\Component\Fluid;
use App\Engine\Component\MapPosition;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\WorldAwareTrait;
use App\System\ForceDirection;
use App\System\World\World;

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

        /** @var MapPosition $oldPosition */
        /** @var Fluid $fluid */
        foreach($fluidEntitiesOnMap as $entityId => [$fluid, $oldPosition]) {
            $newPosition = $this->updateFluidPosition($fluid, $oldPosition, $entityId);
            if($newPosition) { //updated, continue to exists
                $collided = count($this->world->getEntityCollection(...$newPosition)->getEntitiesWithComponents(
                    Collideable::class
                ));

                if ($collided) {
                    $createdFluids = $this->resolveCollision($entityId, $newPosition, $fluid);

                    //todo foreach created fluid, combine all the fluids on the same space
                }
            }
        }
    }

    private function updateFluidPosition(Fluid $fluid, MapPosition $position, int|string $entityId): ?array
    {
        //todo fluid should loose strength on movement. maybe 5%?
        $vectorForce = $fluid->getForceDirection()->getVectorForce();
        $newPosition = [
            $position->getX() + $vectorForce['x'],
            $position->getY() + $vectorForce['y']
        ];

        $this->entityManager->updateEntityComponents($entityId, new MapPosition(...$newPosition));

        if ($this->world->isOutOfBounds(...$newPosition)) {
            $this->entityManager->removeEntity($entityId);
            return null;
        }

        return $newPosition;
    }

    private function removeFluid(int|string $entityId, array $newPosition): void
    {
        $this->entityManager->removeEntity($entityId);
        $this->world->getEntityCollection(...$newPosition)->removeEntity($entityId);
    }

    /**
     * @return Entity[]
     */
    private function resolveCollision(int|string $entityId, array $newPosition, Fluid $fluid): array
    {
        $this->removeFluid($entityId, $newPosition);

        $createdFluids = [];
        $freeDirections = [];
        foreach (ForceDirection::cases() as $possibleDirection) {
            //exclude inverse directions and current direction.
            if (in_array(
                $possibleDirection,
                [
                    $fluid->getForceDirection(),
                    $fluid->getForceDirection()->getPrimaryInverse(),
                    ...$fluid->getForceDirection()->getSecondaryInverses(),
                    ...$fluid->getForceDirection()->getTertiaryInverses(),
                ]
            )
            ) {
                continue;
            }

            $vectorForce = $possibleDirection->getVectorForce();
            $possiblePosition = [
                $newPosition[0] + $vectorForce['x'],
                $newPosition[1] + $vectorForce['y']
            ];
            $possibleCollision = count(
                $this->world->getEntityCollection(...$possiblePosition)->getEntitiesWithComponents(
                    Collideable::class
                ));

            !$possibleCollision && $freeDirections[] = $possibleDirection;
        }

        $freeDirectionsCount = count($freeDirections);
        if ($freeDirectionsCount === 0) { //no Free Directions
            //invert the direction of the current fluid and reduce it's strength by half.
            $newStrength = $fluid->getStrength() * 0.5;
            $newStrength = $newStrength < 0.05 ? 0 : $newStrength;
            if ($newStrength) {
                $createdFluids[] = $this->entityManager->createEntity(
                    new Fluid(
                        $fluid->getForceDirection()->getPrimaryInverse(),
                        $newStrength
                    ),
                    new MapPosition(...$newPosition)
                );
            }

        } else {// free directions, distribute.
            //todo (70% should be based on density of fluid?)
            $strengthToDistribute = $fluid->getStrength() * 0.7;
            $strengthToDistribute = $strengthToDistribute < 0.05 ? 0 : $strengthToDistribute;
            $distributedStrength = $strengthToDistribute / $freeDirectionsCount;
            if ($distributedStrength > 0) {
                foreach ($freeDirections as $freeDirection) {
                    $vectorForce = $freeDirection->getVectorForce();
                    $newFreePosition = [
                        $newPosition[0] + $vectorForce['x'],
                        $newPosition[1] + $vectorForce['y']
                    ];
                    $createdFluids[] = $this->entityManager->createEntity(
                        new Fluid($freeDirection, $distributedStrength),
                        new MapPosition(...$newFreePosition)
                    );
                }
            }
        }

        return $createdFluids;
    }
}
