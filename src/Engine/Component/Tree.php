<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Component\ItemDropper\DropOn;
use App\Engine\Component\ItemDropper\ItemDropper;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Item\ItemManager;

class Tree implements ComponentInterface
{
    static public function createTree(
        EntityManager $entityManager,
        ItemManager $itemManager,
        int $x,
        int $y
    ): Entity {
        return $entityManager->createEntity(
            new Tree(),
            new MapPosition($x, $y),
            new MapSymbol("\033[32m♣\033[0m"),
            new Collideable(),
            new HitPoints(5,5),
            new ItemDropper(
                $itemManager->getItemBlueprintByInternalName('wood'),
                DropOn::DIE,
                3,
                5,
                1
            ),
        );
    }
}
