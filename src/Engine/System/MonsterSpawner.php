<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\Monster;
use App\Engine\Component\ParentSpawner;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\WorldAwareTrait;
use App\System\Biome\BiomePresetLibrary;
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
        private readonly BiomePresetLibrary $biomePresetLibrary,
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
        $biomes = array_filter(
            array_map(
                fn ($bn) => $this->biomePresetLibrary->getBiomeByName($bn),
                $spawner->getBiomes()
            )
        );




        if ($inMapCount < $spawner->getMaxAmount()) {
            //30% of spawning a new monster
            if (rand(0, 100) < $spawner->getChance() * 100) {
                do {

                    [$targetX, $targetY] = $this->getTargetPoint($biomes);

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

    private function getTargetPoint(array $biomes): array
    {
        $targetX = rand(0, $this->world->getWidth() - 1);
        $targetY = rand(0, $this->world->getHeight() - 1);

        $randomBiome = $biomes[rand(0, count($biomes) - 1)] ?? '';

        $biomeCoordinates = $this->world->getLinearBiomeData($randomBiome->getName());

        $randomBiomePoint = $biomeCoordinates[rand(0, count($biomeCoordinates) - 1)] ?? null;


        if ($randomBiomePoint) {
            $targetX = $randomBiomePoint->getX();
            $targetY = $randomBiomePoint->getY();
        }

        return array($targetX, $targetY);
    }
}
