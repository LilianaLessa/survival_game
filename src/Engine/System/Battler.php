<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\HitPoints;
use App\Engine\Component\ItemDropper\DropOn;
use App\Engine\Component\ItemDropper\ItemDropper;
use App\Engine\Component\ItemOnGround;
use App\Engine\Component\MapPosition;
use App\Engine\Entity\EntityManager;

class Battler implements AISystemInterface
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function process(): void
    {
        $this->processDeadEntities();
        // TODO: Implement process() method.
    }

    private function processDeadEntities()
    {
        $hittableEntities = $this->entityManager->getEntitiesWithComponents(
            HitPoints::class,
            MapPosition::class
        );

        /**
         * @var HitPoints $hitPoints
         * @var MapPosition $mapPosition
         */
        foreach ($hittableEntities as $entityId => [$hitPoints, $mapPosition]) {
            if ($hitPoints->getCurrent() < 1) { //dead, process drops
                $deadEntity = $this->entityManager->getEntityById($entityId);
                $deadEntity && $this->processDropsOnDie(
                    $mapPosition,
                    $deadEntity->getComponent(ItemDropper::class)
                );

                $this->entityManager->removeEntity($entityId);
            }
        }
    }

    private function processDropsOnDie(
        MapPosition $mapPosition,
        ?ItemDropper $itemDropper
    ) {
        if ($itemDropper && $itemDropper->getDropOn() === DropOn::DIE) {
            $chance = $itemDropper->getChance(); //0~1
            $dice = mt_rand() / mt_getrandmax();
            if ($dice <= $chance) { //success on drop.
                $amount = rand($itemDropper->getMinAmount(), $itemDropper->getMaxAmount());
                $itemBluePrint = $itemDropper->getItemBlueprint();

                ItemOnGround::createItemOnGround(
                    $this->entityManager,
                    $itemBluePrint,
                    $amount,
                    $mapPosition->getX(),
                    $mapPosition->getY(),
                );
            }
        }
    }
}
