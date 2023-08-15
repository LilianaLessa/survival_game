<?php

declare(strict_types=1);

namespace App\System\AI\Behavior;

class BehaviorEffectConfig
{
    /** @var BehaviorEffectParameterConfig[]  */
    private array $effectParameterConfigs = [];

    public function __construct(
        private readonly BehaviorEffectType $behaviorEffectType,
        BehaviorEffectParameterConfig ...$effectParameterConfigs
    ) {
        $this->effectParameterConfigs = $effectParameterConfigs;
    }

    public function getBehaviorEffectType(): BehaviorEffectType
    {
        return $this->behaviorEffectType;
    }

    public function getEffectParameterConfigs(): array
    {
        return $this->effectParameterConfigs;
    }
}
