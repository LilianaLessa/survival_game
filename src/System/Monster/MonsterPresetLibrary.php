<?php

declare(strict_types=1);

namespace App\System\Monster;

use App\Engine\Component\BehaviorCollection;
use App\System\AI\BehaviorPresetLibrary;
use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\AbstractPresetLibrary;
use App\System\PresetLibrary\PresetDataType;

class MonsterPresetLibrary extends AbstractPresetLibrary
{
    public function __construct(
        private readonly BehaviorPresetLibrary $aiBehaviorManager
    )
    {
    }

    protected function createPreset(?PresetDataType $presetDataType, object $rawPreset): AbstractPreset
    {
        return new MonsterPreset(
            name: $rawPreset->name,
            symbol: $rawPreset->symbol ?? null,
            behaviorCollection: $this->loadBehaviorCollection($rawPreset)
        );
    }

    private function loadBehaviorCollection(object $rawPreset): BehaviorCollection
    {
        $rawBehaviors = $rawPreset->behaviors ?? [];
        $behaviors = [];

        foreach ($rawBehaviors as $rawBehavior) {
            $objectKeys =  array_keys((array)($rawBehavior));
            $behaviorType = $objectKeys[0] ?? '';
            $behaviorName = $rawBehavior->$behaviorType;
            $getter = match ($behaviorType) {
                'preset' =>
                fn ($v) => $this->aiBehaviorManager->getPresetByNameAndTypes(
                    $v,
                    PresetDataType::BEHAVIOR_PRESET
                )[0],
                default => fn ($v) => null,
            };

            $behavior = $getter ? $getter($behaviorName) : null;
            $behavior && $behaviors[] = $behavior;
        }

        return new BehaviorCollection(...$behaviors);
    }

    /** @return PresetDataType[] */
    protected function getPresetTypesToLoad(): array
    {
        return [
            PresetDataType::MONSTER_PRESET
        ];
    }
}
