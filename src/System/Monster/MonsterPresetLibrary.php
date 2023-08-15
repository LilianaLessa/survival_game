<?php

declare(strict_types=1);

namespace App\System\Monster;

use App\Engine\Component\BehaviorCollection;
use App\System\AI\AiBehaviorManager;
use App\System\PresetDataLoaderTrait;
use App\System\PresetDataType;

//todo create a abstract class PresetLibrary. This should be parent of MonsterManager and AiBehaviorManager and
//     determine some abstract methods, like load and so on.
//     maybe better to determine a protected method to build the preset
//     and return it to be added to the collection.
//
//     Todo maybe even making load into a static method and the class itself be a singleton.
class MonsterPresetLibrary
{
    use PresetDataLoaderTrait;

    /** @var MonsterPreset[] */
    private array $presetsByName = [];

    public function __construct(
        private readonly AiBehaviorManager $aiBehaviorManager
    )
    {
    }

    public function load(string $presetDataDirectory): void
    {
        [
            $rawMonsterPresets,
        ] = $this->loadRawPresetData(
            $presetDataDirectory,
            PresetDataType::MONSTER_PRESET,
        );

        //load the presets from the data
        foreach ($rawMonsterPresets as $rawPreset) {
            ($rawPreset->name ?? null) && $this->presetsByName[$rawPreset->name] = new MonsterPreset(
                name: $rawPreset->name,
                symbol: $rawPreset->symbol ?? null,
                behaviorCollection: $this->loadBehaviorCollection($rawPreset)
            );
        }
    }

    public function getPresetByName(string $presetName): ?MonsterPreset
    {
        return $this->presetsByName[$presetName] ?? null;
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
                'preset' => fn ($v) => $this->aiBehaviorManager->getPresetByName($v),
                default => fn ($v) => null,
            };

            $behavior = $getter ? $getter($behaviorName) : null;
            $behavior && $behaviors[] = $behavior;
        }

        return new BehaviorCollection(...$behaviors);
    }
}
