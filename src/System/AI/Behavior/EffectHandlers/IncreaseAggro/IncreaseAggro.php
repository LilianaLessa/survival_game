<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers\IncreaseAggro;

use App\Engine\Component\AggroQueue;
use App\Engine\Component\HitByEntity;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\AI\Behavior\BehaviorEffectConfig;
use App\System\AI\Behavior\BehaviorEffectParameterConfig;
use App\System\AI\Behavior\BehaviorEffectType;
use App\System\AI\Behavior\EffectHandlers\BehaviorEffectHandlerInterface;
use App\System\AI\Behavior\EffectHandlers\EffectParameterInterface;
use App\System\AI\Behavior\EffectHandlers\IncreaseAggro\Parameters\AggroTargetTriggerEntity;
use App\System\AI\Behavior\EffectHandlers\IncreaseAggro\Parameters\AggroTargetType;
use App\System\World\WorldManager;

class IncreaseAggro implements BehaviorEffectHandlerInterface
{

    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function handle(Entity $targetEntity, EffectParameterInterface ...$effectParameters): void
    {
        /** @var ?HitByEntity $hitByEntity */ //todo this component should depend on the type of target.
        $hitByEntity = $targetEntity->getComponent(HitByEntity::class);

        if ($hitByEntity !== null) {
            /** @var AggroQueue $hitByEntity */
            $aggroList = $targetEntity->getComponent(AggroQueue::class) ?? new AggroQueue();

            $aggroList->addAggro($hitByEntity->getEntity()->getId(), 0.1);

            $targetEntity->removeComponent(HitByEntity::class);

            $this->entityManager->updateEntityComponents(
                $targetEntity->getId(),
                $aggroList
            );
        }
    }

    public static function shouldHandle(BehaviorEffectType $effectType): bool
    {
        return $effectType === BehaviorEffectType::INCREASE_AGGRO;
    }

    public static function buildEffectConfig(object $rawConfigData): BehaviorEffectConfig
    {
        return new BehaviorEffectConfig(
            BehaviorEffectType::INCREASE_AGGRO,
            new BehaviorEffectParameterConfig(
                'target',
                AggroTargetType::tryFrom($rawConfigData->target ?? '')
            )
        );
    }

    public static function buildEffectParameters(WorldManager $worldManager, BehaviorEffectParameterConfig ...$effectParameterConfigs): array
    {
        $resultArray = [];

        foreach ($effectParameterConfigs as $effectParameterConfig) {
            $resultArray[$effectParameterConfig->getName()] = $effectParameterConfig->getValue();
        }

        $target = $resultArray['target'] ?? AggroTargetType::TRIGGER_ENTITY;

        return [
            match ($target) {
                AggroTargetType::TRIGGER_ENTITY => new AggroTargetTriggerEntity()
            }
        ];
    }
}
