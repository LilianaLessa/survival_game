<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Commands\MoveEntity;
use App\Engine\Component\Collideable;
use App\Engine\Component\MapPosition;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\WorldAwareTrait;
use App\System\Direction;
use App\System\World;

class MovementApplier implements PhysicsSystemInterface
{
    use WorldAwareTrait;

    public function __construct(private readonly World $world, private readonly EntityManager $entityManager)
    {
    }

    /** @param Entity[] $entityCollection */
    public function process(): void    {
        $entityCollection = $this->entityManager->getEntities();
        //process all move commands for each entity. fulfill only one. if one is fulfilled, remove others.
        foreach ($entityCollection as $entity) {
            //entity has no position: skip
            /** @var MapPosition $position */
            $position = $entity->getComponent(MapPosition::class);
            if (!$position) {
                continue;
            }

            $moved = false;
            $commands = $entity->getCommands();
            foreach ($commands as $index => $command) {
                if ($command instanceof MoveEntity) {
                    if (!$moved) {
                       $moved = true;

                        [$targetX, $targetY] = $this->calculateTargetCoordinates($position, $command);

                        if ($this->validateMovement($position->getX(), $position->getY(), $targetX, $targetY)) {
                            $this->entityManager->updateEntityComponents(
                                $entity->getId(),
                                new MapPosition($targetX, $targetY)
                            );
                        } else {
                            //cant move to the position;
                        }
                    }
                    unset ($commands[$index]);
                }
            }
            $entity->setCommands(...$commands);
        }
    }

    private function calculateTargetCoordinates(MapPosition $position, MoveEntity $command): array
    {
        $diff = match ($command->getDirection()) {
            Direction::UP => [0,-1],
            Direction::DOWN => [0,1],
            Direction::LEFT => [-1,0],
            Direction::RIGHT => [1,0],
        };

        return [
            $position->getX() + $diff[0],
            $position->getY() + $diff[1],
        ];
    }

    private function validateMovement(int $currentX, $currentY, int $targetX, int $targetY): bool {
        if (
            $targetX < 0
            || $targetX > $this->world->getWidth()
            || $targetY < 0
            || $targetY > $this->world->getHeight()
        ) { //out of map bounds
            return false;
        }

        if (!$this->canOverlapOnWorld($targetX, $targetY)) { //target not empty.
            return false;
        }

        $heightDifference = abs(
            $this->getTerrainHeight($currentX, $currentY) - $this->getTerrainHeight($targetX, $targetY)
        );
        if ($heightDifference > 1) { //height gap is too high.
            return false;
        }

        return true;
    }
}
