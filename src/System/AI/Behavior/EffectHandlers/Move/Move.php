<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers\Move;

use App\Engine\Entity\EntityManager;
use App\System\AI\Behavior\BehaviorEffectConfig;
use App\System\AI\Behavior\BehaviorEffectParameter;
use App\System\AI\Behavior\BehaviorEffectType;
use App\System\AI\Behavior\EffectHandlers\BehaviorEffectHandlerInterface;
use App\System\AI\Behavior\EffectHandlers\EffectParameterInterface;
use App\System\AI\Behavior\EffectHandlers\Move\Parameters\TargetCoordinateTypes;

class Move implements BehaviorEffectHandlerInterface
{
    public function __construct(EntityManager $entityManager)
    {
    }

    public function shouldHandle(BehaviorEffectType $effectType): bool
    {
        return $effectType === BehaviorEffectType::MOVE;
    }

    /**
     *  return a array of strings with the required parameter classes,
     *      so the engine can understand what should be injected on the handle method.
     *
     * @return string[]
     */
    public function getRequiredParameterClasses(): array
    {
        return [

        ];
    }

    //Todo maybe this Parameter should be an EffectParameterCollection, so
    //     It can be filtered in the same way of Entity Components are filtered.
    //     So, it would be up to the caller fill this collection with all types of parameters and their values.
    public function handle(EffectParameterInterface ...$effectParameter): void
    {
        //todo it needs a targetCoordinate and a TargetEntityId


        //get TargetCoordinatesInterface
        //get TargetEntity

        //calculates the path to targetCoordinate and add the path to the movement queue of the target entity.

        //todo how to react to changes on the map during the execution of the movement? just stop the movement?

    }

    public static function buildEffectConfig(object $rawConfigData): BehaviorEffectConfig
    {
        return new BehaviorEffectConfig(
            BehaviorEffectType::MOVE,
            new BehaviorEffectParameter(
                'target',
                TargetCoordinateTypes::tryFrom($rawConfigData->target ?? '')
            ),
            new BehaviorEffectParameter(
                'minDistance',
                $rawConfigData->minDistance ?? null
            ),
            new BehaviorEffectParameter(
                'maxDistance',
                $rawConfigData->maxDistance ?? null
            ),
        );
    }
}
