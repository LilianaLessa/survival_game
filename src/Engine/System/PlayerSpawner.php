<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\HitPoints;
use App\Engine\Component\MapPosition;
use App\Engine\Component\Player;
use App\Engine\Entity\EntityManager;
use App\System\Player\PlayerPresetLibrary;
use App\System\World\WorldManager;

class PlayerSpawner implements WorldSystemInterface
{
    public function __construct(
        private readonly WorldManager $worldManager,
        private readonly PlayerPresetLibrary $playerPresetLibrary,
        private readonly EntityManager $entityManager,
    )
    {
    }

    public function process(): void
    {
        $players = $this->entityManager->getEntitiesWithComponents(
            Player::class,
            HitPoints::class,
        );
        /**
         * @var Player $player
         * @var HitPoints $hitPoints
         */
        foreach ($players as $entityId => [ $player, $hitPoints ]) {
            if ($hitPoints->getCurrent() < 1) {
                $this->respawnPlayer($entityId, $hitPoints);
            }

        }

//        if (empty($player)) {
//            $playerPreset = $this->playerPresetLibrary->getDefaultPlayerPreset();
//            $worldWidth = $this->worldManager->getWidth();
//            $worldHeight = $this->worldManager->getHeight();
//
//            Player::createPlayer($this->entityManager, $playerPreset, rand(0,$worldWidth-1),rand(0,$worldHeight-1));
//        }
    }

    private function respawnPlayer(int|string $entityId, HitPoints $hitPoints): void
    {
        $worldWidth = $this->worldManager->getWidth();
        $worldHeight = $this->worldManager->getHeight();

        $this->entityManager->updateEntityComponents(
            $entityId,
            new MapPosition(rand(0, $worldWidth - 1), rand(0, $worldHeight - 1)),
            new HitPoints($hitPoints->getTotal(), $hitPoints->getTotal())
        );
    }
}
