<?php

declare(strict_types=1);

namespace App\System\AI\Behavior;

use App\System\AI\Behavior\EffectHandlers\BehaviorEffectHandlerInterface;

class BehaviorPreset
{
    /**
     * @param BehaviorEffectConfig[] $effectConfigs
     * @param BehaviorTrigger[] $triggers
     */
    public function __construct(
        private readonly string $name,
        private readonly array $effectConfigs,
        private readonly array $triggers,
        private readonly BehaviorTransitions $transitions
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return BehaviorEffectConfig[]
     */
    public function getEffectConfigs(): array
    {
        return $this->effectConfigs;
    }

    public function getTriggers(): array
    {
        return $this->triggers;
    }

    public function getTransitions(): BehaviorTransitions
    {
        return $this->transitions;
    }

}
