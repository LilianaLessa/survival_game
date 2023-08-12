<?php

declare(strict_types=1);

namespace App\Engine\Component\ActionHandler;

use App\Engine\Component\HitPoints;
use App\Engine\Component\MapPosition;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;

class HitTarget implements ActionHandlerInterface
{
    public function execute(EntityManager $entityManger, Entity $targetEntity): void
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
            $entityManger->updateEntityComponents(
                $targetEntity->getId(),
                new HitPoints(
                    $hitPoints->getCurrent() - 1,
                    $hitPoints->getTotal(),
                )
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
