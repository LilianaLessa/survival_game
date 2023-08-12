<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;

class Tree implements ComponentInterface
{
    static public function createTree(EntityManager $entityManager, $x, $y): Entity
    {
        return $entityManager->createEntity(
            new Tree(),
            new MapPosition($x,$y),
            new MapSymbol("\033[32m♣\033[0m"),
            new Collideable(),
        );
    }
}
