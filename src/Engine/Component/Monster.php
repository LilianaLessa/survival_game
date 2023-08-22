<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Component\Item\ItemDropper\DropOn;
use App\Engine\Component\Item\ItemDropper\ItemDropper;
use App\Engine\Component\Item\ItemDropper\ItemDropperCollection;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Item\ItemPresetLibrary;
use App\System\Monster\MonsterDropPreset;
use App\System\Monster\MonsterPreset;

class Monster implements ComponentInterface
{
    public function __construct(private readonly MonsterPreset $monsterPreset)
    {
    }

    public function getMonsterPreset(): MonsterPreset
    {
        return $this->monsterPreset;
    }

    static public function createMonster(
        MonsterPreset $monsterPreset,
        ItemPresetLibrary $itemPresetLibrary,
        EntityManager $entityManager,
        int $x,
        int $y
    ): Entity {
        $totalHitPoints = $monsterPreset->getTotalHitPoints();
        return $entityManager->createEntity(
            new MapSymbol($monsterPreset->getSymbol()),
            new BehaviorCollection(...$monsterPreset->getBehaviorCollection()->getBehaviors()),
            new Monster($monsterPreset),
            new Battler($monsterPreset->getBaseAttackSpeed()),
            new MapPosition($x, $y),
            new Collideable(),
            new MovementQueue($monsterPreset->getBaseMovementSpeed()),
            new HitPoints($totalHitPoints, $totalHitPoints),
            new ItemDropperCollection(
                ...self::createItemDroppers($monsterPreset->getDropCollection(), $itemPresetLibrary)
            )
        );
    }

    /**
     * @param MonsterDropPreset[] $dropCollection
     * @return ItemDropper[]
     */
    private static function createItemDroppers(array $dropCollection, ItemPresetLibrary $itemPresetLibrary): array
    {
        $droppers = [];

        foreach ($dropCollection as $dropPreset) {
            foreach ($dropPreset->getEvents() as $event) {
                $droppers[] = new ItemDropper(
                    $itemPresetLibrary->getPresetByName($dropPreset->getName()),
                    $event->getDropOn(),
                    $event->getMinAmount(),
                    $event->getMaxAmount(),
                    $event->getChance(),
                );
            }
        }

        return $droppers;
    }
}
