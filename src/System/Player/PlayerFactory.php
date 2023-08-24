<?php

declare(strict_types=1);

namespace App\System\Player;

use App\Engine\Component\Player;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\World\WorldManager;

class PlayerFactory
{
    public function __construct(
        private readonly WorldManager $worldManager,
        private readonly EntityManager $entityManager,
    )
    {
    }

    public function create(PlayerPreset $playerPreset, string $socketUuid): Entity
    {
        //todo for now, add always the player on map.

        $worldWidth = $this->worldManager->getWidth();
        $worldHeight = $this->worldManager->getHeight();

        return Player::createPlayer($this->entityManager, $playerPreset, $socketUuid, rand(0,$worldWidth-1),rand(0,$worldHeight-1));


//        return $this->entityManager->createEntity(
//            new Player(),
//            new HitPoints($playerPreset->getTotalHitPoints(), $playerPreset->getTotalHitPoints()),
//            new DefaultColor(ConsoleColorPalette::SYSTEM_YELLOW),
//            new MapSymbol(sprintf("%s", $playerPreset->getDefaultSymbol())),
//            new Collideable(),
//            new MovementQueue($playerPreset->getBaseMovementSpeed()),
//            new WorldActor(),
//            new ActionHandlerList(
//                [
//                    WorldActorActionType::PRIMARY->value => new HitTarget()
//                ]
//            ),
//            new ItemCollector(),
//            new Inventory(),
//        );
    }
}
