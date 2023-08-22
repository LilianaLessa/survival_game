<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\MapPosition;
use App\Engine\Component\Monster;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\WorldAwareTrait;
use App\System\Item\ItemPresetLibrary;
use App\System\Monster\MonsterPresetLibrary;
use App\System\World\WorldManager;

//todo this monster spawner can be a component for a entity on map.
//     then a combination of map area, monster preset holder and spawn rules components would do the rest.
class MonsterSpawner implements WorldSystemInterface
{
    use WorldAwareTrait;

    public function __construct(
        private readonly WorldManager $world,
        private readonly ItemPresetLibrary $itemManager,
        private readonly EntityManager $entityManager,
        private readonly MonsterPresetLibrary $monsterPresetLibrary,
        private readonly int $maxMonstersInMap,
        private readonly string $monsterName,
    ) {
    }

    /** @param Entity[] $entityCollection */
    public function process(): void
    {
        $monsterInMapCount = $this->getMonstersCount();

        $maxMonsterInMap = $this->maxMonstersInMap;

        if ($monsterInMapCount < $maxMonsterInMap) {
            //30% of spawning a new monster
            if (rand(0, 100) < 30) {
                do {
                    $targetX = rand(0, $this->world->getWidth() -1);
                    $targetY = rand(0, $this->world->getHeight() -1);

                    if (!$this->canOverlapOnWorld($targetX, $targetY)) { //target not empty.
                        continue;
                    }

                    $this->spawnMonster($targetX, $targetY, $this->monsterName);

                    break;
                } while (true);
            }
        }
    }

    private function spawnMonster(int $targetX, int $targetY, string $monsterName): void
    {
        $monsterPreset = $this->monsterPresetLibrary->getMonsterPreset($monsterName);

        $monsterPreset && Monster::createMonster(
            $monsterPreset,
            $this->itemManager,
            $this->entityManager,
            $targetX,
            $targetY
        );
    }

    private function getMonstersCount(): int
    {

        $monsterInMap = $this->entityManager->getEntitiesWithComponents(
            Monster::class,
            MapPosition::class
        );
        /** @var Monster $m */
        $monsterInMap = array_filter(
            $monsterInMap,
            fn ($m) => $m[0]->getMonsterPreset()->getName() === $this->monsterName
        );

        return count($monsterInMap);
    }
}
