<?php

declare(strict_types=1);

namespace App\System\AI;

use App\System\AI\Behavior\BehaviorEffectConfig;
use App\System\AI\Behavior\BehaviorEffectType;
use App\System\AI\Behavior\BehaviorPreset;
use App\System\AI\Behavior\BehaviorTransitions;
use App\System\AI\Behavior\BehaviorTrigger;
use App\System\AI\Behavior\EffectHandlers\BehaviorTriggerType;
use App\System\AI\Behavior\EffectHandlers\IncreaseAggro\IncreaseAggro;
use App\System\AI\Behavior\EffectHandlers\Move\Move;
use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\AbstractPresetLibrary;
use App\System\PresetLibrary\PresetDataType;

class BehaviorPresetLibrary extends AbstractPresetLibrary
{
    protected function createPreset(?PresetDataType $presetDataType, object $rawPreset): AbstractPreset
    {
        $behaviorPreset = new BehaviorPreset(
            $rawPreset->name,
            $this->loadEffectConfigs($rawPreset),
            $this->loadEffectTriggers($rawPreset),
            $this->loadBehaviorTransitions($rawPreset),
        );

        $behaviorPreset->setSilent($rawPreset->silent ?? false);

        return $behaviorPreset;
    }

    protected function getPresetTypesToLoad(): array
    {
        return [
            PresetDataType::BEHAVIOR_PRESET,
            //PresetDataType::BEHAVIOR_PRESET_GROUP, //todo implement this loader
        ];
    }

    /**
     * @return BehaviorEffectConfig[]
     */
    private function loadEffectConfigs(object $rawPreset): array
    {
        $rawEffects = $rawPreset->effects ?? [];

        $effectConfigs = [];

        foreach ($rawEffects as $rawEffect) {
            $objectKeys =  array_keys((array)($rawEffect));
            $effectTypeConfigKey = $objectKeys[0] ?? '';
            $effectType = BehaviorEffectType::tryFrom($effectTypeConfigKey);
            $configData = $rawEffect->$effectTypeConfigKey;

            $effectConfig = match ($effectType) {
                BehaviorEffectType::MOVE => Move::buildEffectConfig($configData),
                BehaviorEffectType::INCREASE_AGGRO => IncreaseAggro::buildEffectConfig($configData),
                default => null,
            };

            $effectConfig && $effectConfigs[] = $effectConfig;
        }

        return $effectConfigs;
    }

    /**
     * @return BehaviorTrigger[]
     */
    private function loadEffectTriggers(object $rawPreset): array
    {
        $rawTriggers = (array)($rawPreset->triggers ?? new \stdClass());

        $effectTriggers = [];

        foreach ($rawTriggers as $name => $value) {
            try {
                $behaviorTriggerType = BehaviorTriggerType::tryFrom($name);
            } catch (\Throwable $e) {
              $e = $e;
            }

            $behaviorTriggerType && $effectTriggers[] = new BehaviorTrigger(
                $behaviorTriggerType,
                $value
            );
        }

        return $effectTriggers;
    }

    private function loadBehaviorTransitions(object $rawPreset): BehaviorTransitions
    {
        //todo load it correctly;
        return new BehaviorTransitions([]);
    }
}
