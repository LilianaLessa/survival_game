<?php

declare(strict_types=1);

namespace App\Engine\Component\ActionHandler;

use App\Engine\Component\ColorEffect;
use App\Engine\Component\HitByEntity;
use App\Engine\Component\HitPoints;
use App\Engine\Component\MapPosition;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\ConsoleColor;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;

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
            !$colorEffect && $components[] = new ColorEffect(50, ConsoleColor::Red->value);

            $entityManger->updateEntityComponents(
                $targetEntity->getId(),
                ...$components,
            );

            Dispatcher::dispatch(
                new UiMessageEvent(
                    sprintf(
                        "entity at %d,%d was Hit. %d/%d Id %s\n",
                        ...[
                            ...$targetCoordinates,
                            $hitPoints->getCurrent(),
                            $hitPoints->getTotal(),
                            $targetEntity->getId()
                        ]
                    )
                )
            );
        }
    }
}
