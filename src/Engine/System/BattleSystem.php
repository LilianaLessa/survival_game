<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Commands\MoveEntity;
use App\Engine\Component\AggroQueue;
use App\Engine\Component\Battler;
use App\Engine\Component\AttackTarget;
use App\Engine\Component\ColorEffect;
use App\Engine\Component\HitByEntity;
use App\Engine\Component\HitPoints;
use App\Engine\Component\Item\ItemDropper\DropOn;
use App\Engine\Component\Item\ItemDropper\ItemDropper;
use App\Engine\Component\Item\ItemDropper\ItemDropperCollection;
use App\Engine\Component\Item\ItemOnGround;
use App\Engine\Component\MapPosition;
use App\Engine\Component\Monster;
use App\Engine\Component\MovementQueue;
use App\Engine\Component\MsTimeFromLastAttack;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\ConsoleColor;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\Helpers\RouteService;

class BattleSystem implements AISystemInterface
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
                    $itemBluePrint = $itemDropper->getItemPreset();

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
                if ($this->isInAttackRange($selfPosition, $targetPosition)) {
                    $this->attack($attacker, $target->getEntityToAttack());
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
                                $movementQueue,
                                new MsTimeFromLastAttack((int)floor(microtime(true) * 1000))
                            );
                        }
                    }
                }
            } else { //target not found in map
                //remove target from aggro queue
                /** @var ?AggroQueue $aggroQueue */
                $aggroQueue = $attacker->getComponent(AggroQueue::class);

                $aggroQueue->cleanAggro($target->getEntityToAttack()->getId());

                //remove attack target
                $this->entityManager->removeComponentsFromEntity($entityId, AttackTarget::class);
            }
        }
    }

    private function isInAttackRange(MapPosition $attackerPosition, MapPosition $targetPosition): bool
    {
        $attackerPoint = $attackerPosition->get();
        $targetPoint = $targetPosition->get();

        return $attackerPoint->isAdjacent($targetPoint);
    }

    private function attack(Entity $attacker, Entity $getEntityToAttack): void
    {
        $currentMs = (int)floor(microtime(true) * 1000);

        $msFromLastAttack = $attacker->getComponent(MsTimeFromLastAttack::class) ??
            new MsTimeFromLastAttack($currentMs);

        $delta = $currentMs - $msFromLastAttack->getMsTime();
        /** @var Battler $battler */
        $battler = $attacker->getComponent(Battler::class);

        $updateLastAttack = false;
        if ($delta > 1 && $battler) {
            $deltaS = ($delta) / 1000;

            $attackSpeed = $battler->getBaseAttackSpeed();

            $dueAttacks = (int)($attackSpeed > 0 ? floor($deltaS * $attackSpeed) : 0);

            for ($i = 0; $i < $dueAttacks; $i++) {
                $this->singleAttack($attacker, $getEntityToAttack, $battler);
                $msFromLastAttack = new MsTimeFromLastAttack($currentMs);
                $updateLastAttack = true;
            }
        } else {
            $updateLastAttack = true;
        }

        if ($updateLastAttack) {
            $this->entityManager->updateEntityComponents(
                $attacker->getId(),
                $msFromLastAttack
            );
        }
    }

    private function singleAttack(Entity $attacker, Entity $targetEntity, Battler $battler): void
    {
        /** @var Monster $monster */
        $monster = $attacker->getComponent(Monster::class);

        $monsterName = $monster->getMonsterPreset()->getName();

        $targetName = 'player'; //$targetEntity->getId();

        /** @var ?HitPoints $hitPoints */
        $hitPoints = $targetEntity->getComponent(HitPoints::class);

        $hitMessage = sprintf("%s attacks %s\n", $monsterName, $targetName);

        if ($hitPoints) {
            $damage = 1;
            $newHitPoints = new HitPoints(
                $hitPoints->getCurrent() - $damage,
                $hitPoints->getTotal(),
            );

            $components = [
                $newHitPoints,
                new HitByEntity($attacker)
            ];

            $colorEffect = $targetEntity->getComponent(ColorEffect::class);
            !$colorEffect && $components[] = new ColorEffect(50, ConsoleColor::Red->value);

            $this->entityManager->updateEntityComponents(
                $targetEntity->getId(),
                ...$components
            );

            $hitMessage .= sprintf(
                "\tCaused %d damage. HP: %d/%d\n",
                $damage,
                $newHitPoints->getCurrent(),
                $newHitPoints->getTotal(),
            );
        }

        Dispatcher::dispatch(
            new UiMessageEvent(
                $hitMessage
            )
        );
    }
}
