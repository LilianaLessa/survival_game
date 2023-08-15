<?php

declare(strict_types=1);

namespace App\System\World;

use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\AbstractPresetLibrary;
use App\System\PresetLibrary\PresetDataType;

class WorldPresetLibrary extends AbstractPresetLibrary
{
    protected function createPreset(?PresetDataType $presetDataType, mixed $rawPreset): AbstractPreset
    {
        return new WorldPreset(
            $rawPreset->name,
            $rawPreset->mapWidth ?? 20,
            $rawPreset->mapHeight ?? 20,
        );
    }

    protected function getPresetTypesToLoad(): array
    {
        return [
            PresetDataType::WORLD_PRESET
        ];
    }
}
