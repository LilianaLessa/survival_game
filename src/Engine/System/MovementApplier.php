<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Commands\MoveEntity;
use App\Engine\Component\MapPosition;
use App\Engine\Component\Movable;
use App\Engine\Component\Player;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\WorldAwareTrait;
use App\System\Direction;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\World;

class MovementApplier implements PhysicsSystemInterface
{
    use WorldAwareTrait;

    public function __construct(private readonly World $world, private readonly EntityManager $entityManager)
    {
    }

    /** @param Entity[] $entityCollection */
    public function process(): void    {
        $moveableEntities = $this->entityManager->getEntitiesWithComponents(
            Movable::class,
            MapPosition::class
        );

        //process all move commands for each entity. fulfill only one. if one is fulfilled, remove others.
        /**
         * @var Movable $movable
         * @var MapPosition $position
         */
        foreach ($moveableEntities as $entityId => [$movable, $position]) {
            $moved = false;
            foreach ($movable->getMovementQueue() as $command) {
                if (!$moved) {
                   $moved = true;

                    [$targetX, $targetY] = $this->calculateTargetCoordinates($position, $command);

                    if ($this->validateMovement($position->getX(), $position->getY(), $targetX, $targetY)) {
                        $this->entityManager->updateEntityComponents(
                            $entityId,
                            new MapPosition($targetX, $targetY)
                        );
                    } elseif($this->entityManager->entityHasComponent($entityId,Player::class)) {
                        Dispatcher::getInstance()->dispatch(
                            new UiMessageEvent("Can't move in this direction.\n")
                        );
                    }
                }
            }

            $moved && $movable->clear();
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
            || $targetX >= $this->world->getWidth()
            || $targetY < 0
            || $targetY >= $this->world->getHeight()
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
