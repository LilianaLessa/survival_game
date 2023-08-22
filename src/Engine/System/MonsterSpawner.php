<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\MapPosition;
use App\Engine\Component\Monster;
use App\Engine\Component\ParentSpawner;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\WorldAwareTrait;
use App\System\Item\ItemPresetLibrary;
use App\System\Monster\MonsterPresetLibrary;
use App\System\Monster\Spawner\MonsterSpawnerLibrary;
use App\System\Monster\Spawner\MonsterSpawnerPreset;
use App\System\World\WorldManager;

//todo this monster spawner can be a component for a entity on map.
//     then a combination of map area, monster preset holder and spawn rules components would do the rest.
class MonsterSpawner implements WorldSystemInterface
{
    use WorldAwareTrait;

    public function __construct(
        private readonly WorldManager $world,
        private readonly ItemPresetLibrary $itemPresetLibrary,
        private readonly EntityManager $entityManager,
        private readonly MonsterPresetLibrary $monsterPresetLibrary,
        private readonly MonsterSpawnerLibrary $monsterSpawnerLibrary,
    ) {
    }

    public function process(): void
    {
        $spawners = $this->monsterSpawnerLibrary->getAll();

        foreach ($spawners as $spawner) {
            $this->spawn($spawner);
        }
    }

    private function spawn(MonsterSpawnerPreset $spawner): void
    {
        $children = array_filter(
            $this->entityManager->getEntitiesWithComponents(ParentSpawner::class),
            fn ($c) => $c[0]->getParentSpawner()->getName() === $spawner->getName()
        );

        $inMapCount = count($children);

        if ($inMapCount < $spawner->getMaxAmount()) {
            //30% of spawning a new monster
            if (rand(0, 100) < $spawner->getChance() * 100) {
                do {
                    $targetX = rand(0, $this->world->getWidth() -1);
                    $targetY = rand(0, $this->world->getHeight() -1);

                    if (!$this->canOverlapOnWorld($targetX, $targetY)) { //target not empty.
                        continue;
                    }

                    $this->spawnMonster($spawner, $targetX, $targetY);

                    break;
                } while (true);
            }
        }
    }

    private function spawnMonster(
        MonsterSpawnerPreset $parentSpawner,
        int $targetX,
        int $targetY,
    ): void {
        $monsterPreset = $this->monsterPresetLibrary->getMonsterPreset(
            $parentSpawner->getMonsterPresetName()
        );

        $monsterPreset && Monster::createMonster(
            $parentSpawner,
            $monsterPreset,
            $this->itemPresetLibrary,
            $this->entityManager,
            $targetX,
            $targetY
        );
    }
}
