<?php

declare(strict_types=1);

namespace App\Engine\System;

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
        $player = $this->entityManager->getEntitiesWithComponents(
            Player::class
        );

        if (empty($player)) {
            $playerPreset = $this->playerPresetLibrary->getDefaultPlayerPreset();
            $worldWidth = $this->worldManager->getWidth();
            $worldHeight = $this->worldManager->getHeight();

            Player::createPlayer($this->entityManager, $playerPreset, rand(0,$worldWidth-1),rand(0,$worldHeight-1));
        }
    }
}
