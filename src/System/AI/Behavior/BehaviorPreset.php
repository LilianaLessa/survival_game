<?php

declare(strict_types=1);

namespace App\System\AI\Behavior;

use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\PresetDataType;

class BehaviorPreset extends AbstractPreset
{
    /**
     * @param BehaviorEffectConfig[] $effectConfigs
     * @param BehaviorTrigger[] $triggers
     */
    public function __construct(
        string $name,
        private readonly array $effectConfigs,
        private readonly array $triggers,
        private readonly BehaviorTransitions $transitions
    ) {
        parent::__construct(PresetDataType::BEHAVIOR_PRESET, $name);
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
