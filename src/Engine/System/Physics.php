<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Commands\MoveEntity;
use App\Engine\Component\MapPosition;
use App\Engine\Entity\Entity;
use App\System\Direction;
use App\System\World;

class Physics implements ProcessorSystemInterface
{
    public function __construct(private readonly World $world)
    {
    }

    /** @param Entity[] $entityCollection */
    public function process(array $entityCollection): void    {
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

                        if ($this->validateMovement($targetX, $targetY)) {
                            $entity->addComponent(new MapPosition($targetX, $targetY));
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

    private function validateMovement(int $targetX, int $targetY): bool {
        if (
            $targetX < 0
            || $targetX > $this->world->getWidth()
            || $targetY < 0
            || $targetY > $this->world->getHeight()
        ) { //out of map bounds
            return false;
        }

        $entitiesOnTarget = $this->world->getEntityCollection($targetX, $targetY);
        if (count($entitiesOnTarget)) { //target not empty.
            return false;
        }

        return true;
    }

}
