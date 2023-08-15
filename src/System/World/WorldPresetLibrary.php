<?php

declare(strict_types=1);

namespace App\System\World;

use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\AbstractPresetLibrary;
use App\System\PresetLibrary\PresetDataType;

class WorldPresetLibrary extends AbstractPresetLibrary
{
    public function getDefaultWorldPreset(): WorldPreset
    {
        [ $defaultWorldPreset ] = $this->getPresetByNameAndTypes(
            'defaultWorldPreset',
            PresetDataType::WORLD_PRESET
        );

        return $defaultWorldPreset;
    }

    protected function createPreset(?PresetDataType $presetDataType, object $rawPreset): AbstractPreset
    {
        return (new WorldPreset(
            $rawPreset->name,
            $rawPreset->mapWidth ?? 20,
            $rawPreset->mapHeight ?? 20,
        ))
            ->setScreenUpdaterFps($rawPreset->screenUpdaterFps ?? 10);
    }

    protected function getPresetTypesToLoad(): array
    {
        return [
            PresetDataType::WORLD_PRESET
        ];
    }
}
