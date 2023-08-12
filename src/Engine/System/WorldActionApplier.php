<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\ActionHandler\ActionHandlerList;
use App\Engine\Component\MapPosition;
use App\Engine\Component\WorldActor;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\WorldAwareTrait;
use App\System\Direction;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\World;

class WorldActionApplier implements WorldSystemInterface
{
    use WorldAwareTrait;

    public function __construct(private readonly World $world, private readonly EntityManager $entityManager)
    {
    }

    public function process(): void
    {
        $entities = $this->entityManager->getEntitiesWithComponents(
            WorldActor::class,
            MapPosition::class,
            ActionHandlerList::class
        );

        /**
         * @var WorldActor $worldActor
         * @var MapPosition $mapPosition
         * @var ActionHandlerList $actionHandlers
         */
        foreach ($entities as [$worldActor, $mapPosition, $actionHandlers])
        {
            $actionQueue = $worldActor->getActionQueue();
            foreach ($actionQueue as $action) {
                $targetCoordinates = $this->calculateTargetCoordinates(
                    $action->getDirection(),
                    $mapPosition
                );

                if (!$this->world->isOutOfBounds(...$targetCoordinates)) {
                    $targetEntities = $this->world->getEntityCollection(...$targetCoordinates);
                    foreach ($targetEntities as $targetEntity) {
                        $actionHandlers->getActionByType(
                            WorldActorActionType::tryFrom($action->getActionType())
                        )?->execute($this->entityManager, $targetEntity);
                    }

                    Dispatcher::dispatch(
                        new UiMessageEvent(
                            sprintf(
                                "Action %s => %d,%d\n",
                                $action->getActionType(),
                                ...$targetCoordinates
                            )
                        )
                    );
                }
            }
        }

        $worldActor->clear();
    }

    private function calculateTargetCoordinates(?Direction $direction, MapPosition $from): array
    {
        $diff = match ($direction) {
            Direction::UP => [0,-1],
            Direction::DOWN => [0,1],
            Direction::LEFT => [-1,0],
            Direction::RIGHT => [1,0],
            default => [0,0],
        };

        return [
            $from->getX() + $diff[0],
            $from->getY() + $diff[1],
        ];
    }
}
