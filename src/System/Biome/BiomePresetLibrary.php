<?php

declare(strict_types=1);

namespace App\System\Biome;

use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\AbstractPresetLibrary;
use App\System\PresetLibrary\PresetDataType;

class BiomePresetLibrary extends AbstractPresetLibrary
{

    /**
     * @return BiomePreset[]
     */
    public function getAllGenerationEnabled(): array
    {
        return $this->presetsByTypeAndName[PresetDataType::BIOME_PRESET->value] ?? [];
    }

    public function getBiomeByName(string $biomeName): ?BiomePreset
    {
        $presets = $this->getPresetByNameAndTypes(
            $biomeName,
            PresetDataType::BIOME_PRESET
        );

        return $presets[0] ?? null;
    }

    protected function createPreset(?PresetDataType $presetDataType, object $rawPreset): AbstractPreset
    {
        return new BiomePreset(
            $rawPreset->name,
            $rawPreset->minHeight,
            $rawPreset->minMoisture,
            $rawPreset->minHeat,
            ...($rawPreset->colors ?? [])
        );
    }

    protected function getPresetTypesToLoad(): array
    {
        return [
            PresetDataType::BIOME_PRESET
        ];
    }
}
