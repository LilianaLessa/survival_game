<?php

declare(strict_types=1);

namespace App\System\Player;

use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\AbstractPresetLibrary;
use App\System\PresetLibrary\PresetDataType;

class PlayerPresetLibrary extends AbstractPresetLibrary
{

    public function getDefaultPlayerPreset(): PlayerPreset
    {
        [ $preset ] = $this->getPresetByNameAndTypes(
            'defaultPlayerPreset',
            PresetDataType::PLAYER_PRESET
        );

        return $preset;
    }

    protected function createPreset(?PresetDataType $presetDataType, object $rawPreset): AbstractPreset
    {
        return (new PlayerPreset($rawPreset->name))
            ->setInitialViewportWidth($rawPreset->initialViewportWidth ?? 10)
            ->setInitialViewportHeight($rawPreset->initialViewportHeight ?? 10)
            ->setDefaultSymbol($rawPreset->defaultSymbol ?? "☺")
            ->setTotalHitPoints($rawPreset->totalHitPoints ?? 10)
        ;
    }

    protected function getPresetTypesToLoad(): array
    {
        return [
            PresetDataType::PLAYER_PRESET
        ];
    }
}
