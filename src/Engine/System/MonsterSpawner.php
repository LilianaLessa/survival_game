<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Commands\MoveEntity;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Component\Monster;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Direction;
use App\System\World;

class MonsterSpawner implements WorldSystemInterface
{
    private const MAX_MONSTER_IN_MAP = 3;

    public function __construct(private readonly World $world, private readonly EntityManager $entityManager)
    {
    }

    /** @param Entity[] $entityCollection */
    public function process(array $entityCollection): void
    {
        //check amount of monster in map
        $monsterInMap = array_filter($entityCollection, fn ($e) => $e->getComponent(Monster::class));
        $maxMonsterInMap = self::MAX_MONSTER_IN_MAP;

        if (count($monsterInMap) < $maxMonsterInMap) {
            //30% of spawning a new monster
            if (rand(0, 100) < 30) {
                do {
                    $targetX = rand(0, $this->world->getWidth() -1);
                    $targetY = rand(0, $this->world->getHeight() -1);

                    $entitiesOnTarget = $this->world->getEntityCollection($targetX, $targetY);
                    if (count($entitiesOnTarget)) { //target not empty.
                        continue;
                    }

                    $this->entityManager->addEntity($this->spawnMonster($targetX, $targetY));

                    break;
                } while (true);
            }
        }
    }

    private function spawnMonster(int $targetX, int $targetY): Entity
    {
        return Monster::createMonster(time(), $targetX, $targetY);
    }
}
