<?php

declare(strict_types=1);

namespace App\System\AI\Behavior;

class BehaviorEffectConfig
{
    /** @var BehaviorEffectParameter[]  */
    private array $effectParameters = [];

    public function __construct(
        private readonly BehaviorEffectType $behaviorEffectType,
        BehaviorEffectParameter ...$effectParameters
    ) {
        $this->effectParameters = $effectParameters;
    }

    public function getBehaviorEffectType(): BehaviorEffectType
    {
        return $this->behaviorEffectType;
    }

    public function getEffectParameters(): array
    {
        $resultArray = [];

        foreach ($this->effectParameters as $effectParameter) {
            $resultArray[$effectParameter->getName()] = $effectParameter->getValue();
        }

        return $resultArray;
    }
}
