<?php

declare(strict_types=1);

namespace App\System\Monster\Spawner;

use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\AbstractPresetLibrary;
use App\System\PresetLibrary\PresetDataType;

class MonsterSpawnerLibrary extends AbstractPresetLibrary
{
    /**
     * @return MonsterSpawnerPreset[]
     */
    public function getAll(): array
    {
        return $this->presetsByTypeAndName[PresetDataType::MONSTER_SPAWNER->value] ?? [];
    }

    protected function createPreset(?PresetDataType $presetDataType, object $rawPreset): AbstractPreset
    {
        return new MonsterSpawnerPreset(
            $rawPreset->name,
            $rawPreset->monster,
            $rawPreset->max ?? 0,
            $rawPreset->chance ?? 0,
        );
    }

    protected function getPresetTypesToLoad(): array
    {
        return [
            PresetDataType::MONSTER_SPAWNER
        ];
    }
}
