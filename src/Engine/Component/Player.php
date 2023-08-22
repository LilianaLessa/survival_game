<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Component\ActionHandler\ActionHandlerList;
use App\Engine\Component\ActionHandler\HitTarget;
use App\Engine\Component\Item\Inventory;
use App\Engine\Component\Item\ItemCollector;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\Engine\System\WorldActorActionType;
use App\System\Player\PlayerPreset;

class Player implements ComponentInterface
{
    static public function createPlayer(EntityManager $entityManager, PlayerPreset $playerPreset, $x, $y): Entity
    {
        return $entityManager->createEntity(
            new Player(),
            new MapPosition($x,$y),
            new MapSymbol(sprintf("\033[1;33m%s\033[0m", $playerPreset->getDefaultSymbol())),
            new Collideable(),
            new MovementQueue(10),
            new WorldActor(),
            new ActionHandlerList(
                [
                    WorldActorActionType::PRIMARY->value => new HitTarget()
                ]
            ),
            new ItemCollector(),
            new Inventory(),
        );
    }
}
