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
use App\System\Helpers\ConsoleColorPalette;
use App\System\Player\PlayerPreset;

//todo basically PlayerCommandQueue also represents a player. so maybe this class is not necessary.
class Player implements ComponentInterface
{
    static public function createPlayer(
        EntityManager $entityManager,
        PlayerPreset $playerPreset,
        string $socketUuid,
        $x,
        $y
    ): Entity {
        return $entityManager->createEntity(
            new Player(),
            new PlayerCommandQueue($socketUuid),
            new InGameName(sprintf('<Player - %s>', $socketUuid)),
            new HitPoints($playerPreset->getTotalHitPoints(), $playerPreset->getTotalHitPoints()),
            new MapViewPort($playerPreset->getInitialViewportWidth(), $playerPreset->getInitialViewportHeight()),
            new MapPosition($x, $y),
            new DefaultColor(ConsoleColorPalette::SYSTEM_YELLOW),
            new MapSymbol(sprintf("%s", $playerPreset->getDefaultSymbol())),
            new Collideable(),
            new MovementQueue($playerPreset->getBaseMovementSpeed()),
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
