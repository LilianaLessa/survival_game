<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Component\Item\ItemDropper\DropOn;
use App\Engine\Component\Item\ItemDropper\ItemDropper;
use App\Engine\Component\Item\ItemDropper\ItemDropperCollection;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Item\ItemManager;

class Monster implements ComponentInterface
{
    static public function createMonster(ItemManager $itemManager, EntityManager $entityManager, $x, $y): Entity
    {
        return $entityManager->createEntity(
            new Monster(),
            new MapPosition($x, $y),
            new MapSymbol("♞"),
            new Collideable(),
            new Movable(),
            new HitPoints(10, 10),
            new ItemDropperCollection(
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('tatteredCloth'),
                    DropOn::DIE,
                    1,
                    1,
                    0.8
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('rustyDagger'),
                    DropOn::DIE,
                    1,
                    1,
                    0.7
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('monsterHide'),
                    DropOn::DIE,
                    1,
                    1,
                    0.6
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('shimmeringScale'),
                    DropOn::DIE,
                    1,
                    1,
                    0.3
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('crackedCrystal'),
                    DropOn::DIE,
                    1,
                    1,
                    0.25
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('enchantedInk'),
                    DropOn::DIE,
                    1,
                    1,
                    0.2
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('ancientRelic'),
                    DropOn::DIE,
                    1,
                    1,
                    0.05
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('mangledFur'),
                    DropOn::DIE,
                    1,
                    1,
                    0.9
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('slimyGoo'),
                    DropOn::DIE,
                    1,
                    1,
                    0.9
                ),
            )
        );
    }
}
