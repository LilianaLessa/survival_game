<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\MapPosition;
use App\Engine\Component\MovementQueue;
use App\Engine\Component\Player;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\WorldAwareTrait;
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
        $movableEntities = $this->entityManager->getEntitiesWithComponents(
            MovementQueue::class,
            MapPosition::class
        );

        //process all move commands for each entity. fulfill only one. if one is fulfilled, remove others.
        /**
         * @var MovementQueue $movable
         * @var MapPosition $position
         */
        foreach ($movableEntities as $entityId => [$movable, $position]) {
            $next = $movable->dequeue();
            if (!$next) {
                continue;
            }

            [$targetX, $targetY] = $next->getCoordinates()->toArray();

            if ($this->validateMovement($position->getX(), $position->getY(), $targetX, $targetY)) {
                $this->entityManager->updateEntityComponents(
                    $entityId,
                    new MapPosition($targetX, $targetY)
                );
            } else {
                if($this->entityManager->entityHasComponent($entityId,Player::class)) {
                    Dispatcher::getInstance()->dispatch(
                        new UiMessageEvent("Can't move in this direction.\n")
                    );
                }
                $movable->clear();
                continue;
            }

            $this->entityManager->updateEntityComponents($entityId, $movable);
        }

        //$moved && $movable->clear();

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
