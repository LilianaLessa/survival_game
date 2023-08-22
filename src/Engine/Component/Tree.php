<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Component\Item\ItemDropper\DropOn;
use App\Engine\Component\Item\ItemDropper\ItemDropper;
use App\Engine\Component\Item\ItemDropper\ItemDropperCollection;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Item\ItemPresetLibrary;

class Tree implements ComponentInterface
{
    static public function createTree(
        EntityManager     $entityManager,
        ItemPresetLibrary $itemManager,
        int               $x,
        int               $y
    ): Entity {
        return $entityManager->createEntity(
            new Tree(),
            new MapPosition($x, $y),
           // new MapSymbol("\033[32m♣\033[0m"),
            new MapSymbol("♣"),
            new Collideable(),
            new HitPoints(5,5),
            new ItemDropperCollection(
                new ItemDropper(
                    $itemManager->getPresetByName('wood'),
                    DropOn::DIE,
                    3,
                    5,
                    1
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('treeLeaves'),
                    DropOn::DIE,
                    5,
                    10,
                    1
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('treeSeed'),
                    DropOn::DIE,
                    1,
                    1,
                    0.4
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('rottenTwig'),
                    DropOn::DIE,
                    1,
                    2,
                    0.8
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('insectCarcass'),
                    DropOn::DIE,
                    1,
                    2,
                    0.7
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('honey'),
                    DropOn::DIE,
                    1,
                    1,
                    0.05
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('enchantedFruit'),
                    DropOn::DIE,
                    1,
                    1,
                    0.01
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('eternalSap'),
                    DropOn::DIE,
                    1,
                    1,
                    0.005
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('sylvanHeart'),
                    DropOn::DIE,
                    1,
                    1,
                    0.005
                ),
            ),
        );
    }
}
