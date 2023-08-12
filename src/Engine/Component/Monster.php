<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;

class Monster implements ComponentInterface
{
    static public function createMonster(EntityManager $entityManager, $x, $y): Entity
    {
        return $entityManager->createEntity(
            new Monster(),
            new MapPosition($x,$y),
            new MapSymbol("\033[31m♞\033[0m"),
            new Collideable(),
            new Movable(),
        );
    }
}
