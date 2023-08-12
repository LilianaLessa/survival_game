<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Commands\MoveEntity;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Component\Monster;
use App\Engine\Component\Movable;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Direction;
use App\System\World;

class MonsterController implements AISystemInterface
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    /** @param Entity[] $entityCollection */
    public function process(): void
    {
        $monsterInMap = $this->entityManager->getEntitiesWithComponents(
            Movable::class,
            Monster::class,
            MapPosition::class
        );

        /**
         * @var Movable $movementQueue
         */
        foreach ($monsterInMap as [$movementQueue]) {
            if (rand(0, 100) < 30) {
                $movementQueue->add(match (rand(1,4)) {
                    1 => new MoveEntity(Direction::UP),
                    2 => new MoveEntity(Direction::DOWN),
                    3 => new MoveEntity(Direction::LEFT),
                    4 => new MoveEntity(Direction::RIGHT),
                });
            }
        }
    }
}
