<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Component\ActionHandler\ActionHandlerList;
use App\Engine\Component\ActionHandler\HitTarget;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\Engine\System\WorldActorActionType;

class Player implements ComponentInterface
{
    static public function createPlayer(EntityManager $entityManager, $x, $y): Entity
    {
        return $entityManager->createEntity(
            new Player(),
            new MapPosition($x,$y),
            new MapSymbol("\033[1;33m☺\033[0m"),
            new Collideable(),
            new Movable(),
            new WorldActor(),
            new ActionHandlerList(
                [
                    WorldActorActionType::PRIMARY->value => new HitTarget()
                ]
            )
        );
    }
}
