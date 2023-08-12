<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Component\Item\ItemDropper\DropOn;
use App\Engine\Component\Item\ItemDropper\ItemDropper;
use App\Engine\Component\Item\ItemDropper\ItemDropperCollection;
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
            new ItemDropperCollection(
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('wood'),
                    DropOn::DIE,
                    3,
                    5,
                    1
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('treeLeaves'),
                    DropOn::DIE,
                    5,
                    10,
                    1
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('treeSeed'),
                    DropOn::DIE,
                    1,
                    1,
                    0.4
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('enchantedFruit'),
                    DropOn::DIE,
                    1,
                    1,
                    0.1
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('rottenTwig'),
                    DropOn::DIE,
                    1,
                    2,
                    0.8
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('insectCarcass'),
                    DropOn::DIE,
                    1,
                    2,
                    0.7
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('eternalSap'),
                    DropOn::DIE,
                    1,
                    1,
                    0.05
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('sylvanHeart'),
                    DropOn::DIE,
                    1,
                    1,
                    0.05
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('honey'),
                    DropOn::DIE,
                    1,
                    1,
                    0.3
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('enchantedFruit'),
                    DropOn::DIE,
                    1,
                    1,
                    0.1
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('rottenTwig'),
                    DropOn::DIE,
                    1,
                    2,
                    0.8
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('insectCarcass'),
                    DropOn::DIE,
                    1,
                    2,
                    0.7
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('eternalSap'),
                    DropOn::DIE,
                    1,
                    1,
                    0.05
                ),
                new ItemDropper(
                    $itemManager->getItemBlueprintByInternalName('sylvanHeart'),
                    DropOn::DIE,
                    1,
                    1,
                    0.05
                ),
            ),
        );
    }
}
