<?php

declare(strict_types=1);

namespace App\System\AI;

//This class is responsible for loading the behavior presets and preset groups.
use App\System\AI\Behavior\BehaviorEffectConfig;
use App\System\AI\Behavior\BehaviorEffectType;
use App\System\AI\Behavior\BehaviorPreset;
use App\System\AI\Behavior\BehaviorTransitions;
use App\System\AI\Behavior\BehaviorTrigger;
use App\System\AI\Behavior\EffectHandlers\BehaviorTriggerType;
use App\System\AI\Behavior\EffectHandlers\Move\Move;

class AIBehaviorManager
{
    /** @var BehaviorPreset[] */
    private array $presetsByName = [];

    public function __construct(
        private readonly string $aiBehaviorDataDirectory
    )
    {
    }

    public function load(): void
    {
        [
            $rawPresets,
            $rawPresetGroups,
        ] = $this->loadRawBehaviorData();

        //load the presets from the data
        foreach ($rawPresets as $rawPreset) {
            ($rawPreset->name ?? null) && $this->presetsByName[$rawPreset->name] = new BehaviorPreset(
                $rawPreset->name,
                $this->loadEffectConfigs($rawPreset),
                $this->loadEffectTriggers($rawPreset),
                $this->loadBehaviorTransitions($rawPreset),
            );
        }

        //todo then, load the preset groups.
    }

    public function getPresetByName(string $presetName): ?BehaviorPreset
    {
        return $this->presetsByName[$presetName] ?? null;
    }

    /**
     * @return void
     */
    private function loadRawBehaviorData(): array
    {
        $presets = [];
        $presetGroups = [];

        $aiDirectory = new \DirectoryIterator($this->aiBehaviorDataDirectory);
        foreach ($aiDirectory as $fileInfo) {
            if ($fileInfo->isFile() && $fileInfo->getExtension() === 'json') {
                $jsonData = file_get_contents($fileInfo->getRealPath());
                $rawData = json_decode($jsonData, false);
                $rawObjectCollection = is_object($rawData) ? [$rawData] : $rawData;
                foreach ($rawObjectCollection as $rawObject) {
                    $objectType = $rawObject?->type;
                    if ($objectType) {
                        (match (BehaviorAiConfigType::tryFrom($objectType)) {
                            BehaviorAiConfigType::PRESET =>
                                function ($o) use (&$presets) { $presets[] = $o; },
                            BehaviorAiConfigType::PRESET_GROUP =>
                                function ($o) use (&$presetGroups) { $presetGroups[] = $o;},
                            default => fn ($o) => []
                        })($rawObject);
                    }
                }
            }
        }
        return [
            $presets,
            $presetGroups,
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
            $behaviorTriggerType = BehaviorTriggerType::tryFrom($name);
            $behaviorTriggerType && $effectTriggers[] = new BehaviorTrigger(
                $behaviorTriggerType,
                $value
            );
        }

        return $effectTriggers;
    }

    private function loadBehaviorTransitions(object $rawPreset): BehaviorTransitions
    {
        return new BehaviorTransitions();
    }
}
