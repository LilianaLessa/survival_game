<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Commands\MoveEntity;
use App\Engine\Component\AttackTarget;
use App\Engine\Component\HitPoints;
use App\Engine\Component\Item\ItemDropper\DropOn;
use App\Engine\Component\Item\ItemDropper\ItemDropper;
use App\Engine\Component\Item\ItemDropper\ItemDropperCollection;
use App\Engine\Component\Item\ItemOnGround;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MovementQueue;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Helpers\RouteService;

class Battler implements AISystemInterface
{
    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly RouteService $routeService
    )
    {
    }

    public function process(): void
    {
        $this->processDeadEntities();

        $this->processAttackTargets();
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
                    ...array_filter(
                        [
                            $deadEntity->getComponent(ItemDropper::class),
                            ...$deadEntity->getComponent(
                                ItemDropperCollection::class
                            )?->getItemDroppers() ?? [],
                        ]
                    )
                );

                $this->entityManager->removeEntity($entityId);
            }
        }
    }

    private function processDropsOnDie(
        MapPosition $mapPosition,
        ItemDropper ...$itemDroppers
    ) {
        foreach ($itemDroppers as $itemDropper) {
            if ($itemDropper->getDropOn() === DropOn::DIE) {
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

    private function processAttackTargets(): void
    {
        $entitiesWithAttackTargets = $this->entityManager->getEntitiesWithComponents(
            AttackTarget::class,
            MapPosition::class
        );

        /**
         * @var AttackTarget $target
         * @var MapPosition $selfPosition
         */
        foreach ($entitiesWithAttackTargets as $entityId => [$target, $selfPosition]) {
            $attacker = $this->entityManager->getEntityById($entityId);
            /** @var MapPosition $targetPosition */
            $targetPosition = $target->getEntityToAttack()->getComponent(MapPosition::class);
            if ($targetPosition) {
                if ($this->isInAttackRange($attacker, $targetPosition)) {
                    //todo attack
                } else {
                    /** @var ?MovementQueue $movementQueue */
                    $movementQueue = $attacker->getComponent(MovementQueue::class);
                    if ($movementQueue) {
                        $route = $this->routeService->calculateRoute(
                            $selfPosition->get(),
                            $targetPosition->get()
                        );

                        if (count($route) > 0) {
                            $movementQueue->clear();

                            forEach ($route as $coordinates) {
                                $movementQueue->add(new MoveEntity($coordinates));
                            }

                            $this->entityManager->updateEntityComponents(
                                $attacker->getId(),
                                $movementQueue
                            );
                        }
                    }
                }
            }
        }
    }

    private function isInAttackRange(Entity $attacker, MapPosition $targetPosition): bool
    {
        return false; //todo
    }
}
