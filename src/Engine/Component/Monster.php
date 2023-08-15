<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Component\Item\ItemDropper\DropOn;
use App\Engine\Component\Item\ItemDropper\ItemDropper;
use App\Engine\Component\Item\ItemDropper\ItemDropperCollection;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Item\ItemPresetLibrary;
use App\System\Monster\MonsterPreset;

class Monster implements ComponentInterface
{
    static public function createMonster(
        MonsterPreset $monsterPreset,
        ItemPresetLibrary $itemManager,
        EntityManager $entityManager,
        int $x,
        int $y
    ): Entity {
        return $entityManager->createEntity(
            new MapSymbol($monsterPreset->getSymbol()),
            new BehaviorCollection(...$monsterPreset->getBehaviorCollection()->getBehaviors()),
            new Monster(),
            new MapPosition($x, $y),
            new Collideable(),
            new MovementQueue(),
            new HitPoints(10, 10),
            new ItemDropperCollection(
                new ItemDropper(
                    $itemManager->getPresetByName('tatteredCloth'),
                    DropOn::DIE,
                    1,
                    1,
                    0.8
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('rustyDagger'),
                    DropOn::DIE,
                    1,
                    1,
                    0.7
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('monsterHide'),
                    DropOn::DIE,
                    1,
                    1,
                    0.6
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('shimmeringScale'),
                    DropOn::DIE,
                    1,
                    1,
                    0.3
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('crackedCrystal'),
                    DropOn::DIE,
                    1,
                    1,
                    0.25
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('enchantedInk'),
                    DropOn::DIE,
                    1,
                    1,
                    0.2
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('ancientRelic'),
                    DropOn::DIE,
                    1,
                    1,
                    0.05
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('mangledFur'),
                    DropOn::DIE,
                    1,
                    1,
                    0.9
                ),
                new ItemDropper(
                    $itemManager->getPresetByName('slimyGoo'),
                    DropOn::DIE,
                    1,
                    1,
                    0.9
                ),
            )
        );
    }
}
