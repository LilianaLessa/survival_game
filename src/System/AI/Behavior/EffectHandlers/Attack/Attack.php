<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers\Attack;

use App\Engine\Component\AggroQueue;
use App\Engine\Component\AttackTarget;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\AI\Behavior\BehaviorEffectConfig;
use App\System\AI\Behavior\BehaviorEffectParameterConfig;
use App\System\AI\Behavior\BehaviorEffectType;
use App\System\AI\Behavior\EffectHandlers\Attack\Parameters\AttackTargetType;
use App\System\AI\Behavior\EffectHandlers\Attack\Parameters\AttackTopAggroEntity;
use App\System\AI\Behavior\EffectHandlers\BehaviorEffectHandlerInterface;
use App\System\AI\Behavior\EffectHandlers\EffectParameterInterface;
use App\System\World\WorldManager;

class Attack implements BehaviorEffectHandlerInterface
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function handle(Entity $targetEntity, EffectParameterInterface ...$effectParameters): void
    {
        /** @var ?AggroQueue $aggroQueue */
        $aggroQueue = $targetEntity->getComponent(AggroQueue::class);
        $targetId = $aggroQueue?->getAggroListTop() ?? '';
        $entityToAttack = $this->entityManager->getEntityById($targetId);
        if ($entityToAttack) {
            //add attack target to entity, so the battler can take care of the AI.
            $this->entityManager->updateEntityComponents(
                $targetEntity->getId(),
                new AttackTarget($entityToAttack)
            );
        }


    }

    public static function shouldHandle(BehaviorEffectType $effectType): bool
    {
        return $effectType === BehaviorEffectType::ATTACK;
    }

    public static function buildEffectConfig(object $rawConfigData): BehaviorEffectConfig
    {
        return new BehaviorEffectConfig(
            BehaviorEffectType::ATTACK,
            new BehaviorEffectParameterConfig(
                'target',
                AttackTargetType::tryFrom($rawConfigData->target ?? '')
            ),
        );
    }

    public static function buildEffectParameters(WorldManager $worldManager, BehaviorEffectParameterConfig ...$effectParameterConfigs): array
    {
        $resultArray = [];

        foreach ($effectParameterConfigs as $effectParameterConfig) {
            $resultArray[$effectParameterConfig->getName()] = $effectParameterConfig->getValue();
        }

        $target = $resultArray['target'] ?? AttackTargetType::TOP_AGGRO_ENTITY;

        return [
            match ($target) {
                AttackTargetType::TOP_AGGRO_ENTITY => new AttackTopAggroEntity()
            }
        ];
    }
}
