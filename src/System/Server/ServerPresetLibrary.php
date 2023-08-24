<?php

declare(strict_types=1);

namespace App\System\Server;

use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\AbstractPresetLibrary;
use App\System\PresetLibrary\PresetDataType;

class ServerPresetLibrary extends AbstractPresetLibrary
{
    protected function createPreset(?PresetDataType $presetDataType, object $rawPreset): AbstractPreset
    {
        $serverPreset = new ServerPreset(
            $rawPreset->name,
            $rawPreset->type ?? 'mapServer',
        );

        $serverPreset->setHost($rawPreset->host ?? '127.0.0.1');
        $serverPreset->setPort($rawPreset->port ?? '1988');

        return $serverPreset;
    }

    protected function getPresetTypesToLoad(): array
    {
        return [
            PresetDataType::SERVER_CONFIG,
        ];
    }
}
