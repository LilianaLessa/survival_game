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

    public function getMonsterPreset(string $monsterName): ?MonsterPreset
    {
        $presets = $this->getPresetByNameAndTypes(
            $monsterName,
            PresetDataType::MONSTER_PRESET
        );

        return $presets[0] ?? null;
    }

    protected function createPreset(?PresetDataType $presetDataType, object $rawPreset): AbstractPreset
    {
        $monsterPreset = new MonsterPreset(
            name: $rawPreset->name,
            symbol: $rawPreset->symbol ?? null,
            behaviorCollection: $this->loadBehaviorCollection($rawPreset)
        );

        $monsterPreset->setBaseMovementSpeed($rawPreset->baseMovementSpeed ?? 0);
        $monsterPreset->setBaseAttackSpeed($rawPreset->baseAttackSpeed ?? 0);
        $monsterPreset->setTotalHitPoints($rawPreset->totalHitPoints ?? 1);

        return $monsterPreset;
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
