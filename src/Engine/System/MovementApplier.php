<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\CurrentChunk;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MovementQueue;
use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\WorldAwareTrait;
use App\System\Event\Dispatcher;
use App\System\Event\Event\DebugMessageEvent;
use App\System\Event\Event\PlayerChunkUpdated;
use App\System\Event\Event\UiMessageEvent;
use App\System\World\WorldManager;

class MovementApplier implements PhysicsSystemInterface
{
    use WorldAwareTrait;

    public function __construct(
        private readonly WorldManager $world,
        private readonly EntityManager $entityManager
    )
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
         * @var MovementQueue $movementQueue
         * @var MapPosition $position
         */
        foreach ($movableEntities as $entityId => [$movementQueue, $position]) {
            //calculate how many steps this entity should walk
            $currentMs = (int) floor(microtime(true) * 1000);
            $msLastMovement = $movementQueue->getMsLastMovement() ?? $currentMs - 1000;
            $dueSteps = $movementQueue->getMsLastMovement() ? null : 1;

            $deltaS = ($currentMs - $msLastMovement) / 1000;

            $movementSpeed = $movementQueue->getBaseMovementSpeed(); //speed in cells p/ second

            $dueSteps === null && $dueSteps = (int)($movementSpeed > 0 ? floor($deltaS * $movementSpeed) : 0);

            for ($i = 0; $i < $dueSteps; $i++) {
                $this->step($entityId, $movementQueue, $position);
            }
        }

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

    private function step(int|string $entityId, MovementQueue $movementQueue, MapPosition $position): void
    {
        $next = $movementQueue->dequeue();
        if (!$next) {
            return;
        }

        [$targetX, $targetY] = $next->getCoordinates()->toArray();

        /** @var ?PlayerCommandQueue $playerCommandQueue */
        /** @var ?CurrentChunk $currentChunk */
        [$playerCommandQueue, $currentChunk] = $this->entityManager->getComponentsFromEntityId(
            $entityId,
            PlayerCommandQueue::class,
            CurrentChunk::class
        );

        if ($this->validateMovement($position->getX(), $position->getY(), $targetX, $targetY)) {
            $this->entityManager->updateEntityComponents(
                $entityId,
                new MapPosition($targetX, $targetY)
            );
            if ($playerCommandQueue) {
                $chunkW = $this->world->getWorldChunkWidht();
                $chunkH = $this->world->getWorldChunkHeight();

                $currentChunkId = $this->world->getChunkNumber($targetX, $targetY);
                $uiMessage = sprintf(
                    "Chunk c:%d",
                    $currentChunkId
                );

                $adjacentChunksCoordinates = [
                    'u' => [$targetX, $targetY - $chunkH],
                    'r' => [$targetX + $chunkW, $targetY],
                    'd' => [$targetX, $targetY + $chunkH],
                    'l' => [$targetX - $chunkW, $targetY],
                ];

                foreach ($adjacentChunksCoordinates as $chunk => $coordinates) {
                    if (!$this->world->isOutOfBounds(...$coordinates)) {
                        $uiMessage .= sprintf(
                            " %s:%d",
                            $chunk,
                            $this->world->getChunkNumber(...$coordinates)
                        );
                    }
                }

                $updatedCurrentChunk = $currentChunk ? null : new CurrentChunk($currentChunkId);
                $changedChunk = false; //todo this is not really working, only when warping to 10 10 and then after??
                if ($currentChunk && $currentChunk->getId() !== $currentChunkId) {
                    $updatedCurrentChunk = new CurrentChunk($currentChunkId);
                    $changedChunk = true;

                }

                $updatedCurrentChunk && $this->entityManager->updateEntityComponents(
                    $entityId,
                    $updatedCurrentChunk,
                );

                $changedChunk && Dispatcher::dispatch(new PlayerChunkUpdated($playerCommandQueue));
                
                $uiMessage .= "\n";

                Dispatcher::dispatch(new DebugMessageEvent($uiMessage, $playerCommandQueue));
            }

        } else {

            //todo avoid self collision.
            if ($playerCommandQueue) {
                $uiMessage = "Can't move in this direction.\n";
                Dispatcher::getInstance()->dispatch(new UiMessageEvent($uiMessage, $playerCommandQueue));
            }
            $movementQueue->clear();
            return;
        }

        $movementQueue->setMsLastMovement((int) floor(microtime(true) * 1000));

        $this->entityManager->updateEntityComponents($entityId, $movementQueue);
    }
}
