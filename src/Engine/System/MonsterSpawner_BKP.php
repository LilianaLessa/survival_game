<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\MapPosition;
use App\Engine\Component\Monster;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\WorldAwareTrait;
use App\System\Item\ItemManager;
use App\System\World;

//todo this monster spawner can be a component for a entity on map.
//     then a combination of map area, monster preset holder and spawn rules components would do the rest.
class MonsterSpawner_BKP implements WorldSystemInterface
{
    use WorldAwareTrait;

    public function __construct(
        private readonly World $world,
        private readonly ItemManager $itemManager,
        private readonly EntityManager $entityManager,
        private readonly int $maxMonstersInMap
    ) {
    }

    /** @param Entity[] $entityCollection */
    public function process(): void
    {
        //check amount of monster in map
        $monsterInMap = $this->entityManager->getEntitiesWithComponents(
            Monster::class,
            MapPosition::class
        );
        $maxMonsterInMap = $this->maxMonstersInMap;

        if (count($monsterInMap) < $maxMonsterInMap) {
            //30% of spawning a new monster
            if (rand(0, 100) < 30) {
                do {
                    $targetX = rand(0, $this->world->getWidth() -1);
                    $targetY = rand(0, $this->world->getHeight() -1);

                    if (!$this->canOverlapOnWorld($targetX, $targetY)) { //target not empty.
                        continue;
                    }

                    $this->spawnMonster($targetX, $targetY);

                    break;
                } while (true);
            }
        }
    }

    private function spawnMonster(int $targetX, int $targetY): Entity
    {
        return Monster::createMonster(
            $this->itemManager,
            $this->entityManager,
            $targetX,
            $targetY
        );
    }
}
