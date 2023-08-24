<?php

declare(strict_types=1);

namespace App\Engine\Component\ActionHandler;

use App\Engine\Component\ColorEffect;
use App\Engine\Component\HitByEntity;
use App\Engine\Component\HitPoints;
use App\Engine\Component\MapPosition;
use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Event\Dispatcher;
use App\System\Event\Event\DebugMessageEvent;
use App\System\Event\Event\UpdatePlayerCurrentTarget;
use App\System\Helpers\ConsoleColorPalette;

class HitTarget implements ActionHandlerInterface
{
    public function execute(EntityManager $entityManger, Entity $targetEntity, Entity $actorEntity): void
    {
        /** @var ?HitPoints $hitPoints */
        $hitPoints = $targetEntity->getComponent(HitPoints::class);
        /** @var ?MapPosition $mapPosition */
        $mapPosition = $targetEntity->getComponent(MapPosition::class);
        $targetCoordinates = [
            $mapPosition?->getX() ?? 0,
            $mapPosition?->getY() ?? 0,
        ];

        if ($hitPoints) {
            $components = [
                new HitPoints(
                    $hitPoints->getCurrent() - 1,
                    $hitPoints->getTotal(),
                ),
                new HitByEntity($actorEntity),
            ];

            $colorEffect = $targetEntity->getComponent(ColorEffect::class);
            !$colorEffect && $components[] = new ColorEffect(50, ConsoleColorPalette::SYSTEM_RED);

            $entityManger->updateEntityComponents(
                $targetEntity->getId(),
                ...$components,
            );

            /** @var ?PlayerCommandQueue $playerCommandQueue */
            $playerCommandQueue = $actorEntity->getComponent(PlayerCommandQueue::class);

            if ($playerCommandQueue) {
                $uiMessage = sprintf(
                    "entity at %d,%d was Hit. %d/%d Id %s\n",
                    ...[
                        ...$targetCoordinates,
                        $hitPoints->getCurrent(),
                        $hitPoints->getTotal(),
                        $targetEntity->getId()
                    ]
                );

                Dispatcher::dispatch(new DebugMessageEvent($uiMessage, $playerCommandQueue));

                Dispatcher::dispatch(
                    new UpdatePlayerCurrentTarget($playerCommandQueue, $targetEntity)
                );
            }
        }
    }
}
