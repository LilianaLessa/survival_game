<?php

declare(strict_types=1);

namespace App\System\PresetLibrary;

enum PresetDataType: string
{
    case BEHAVIOR_PRESET = 'behaviorPreset';
    case BEHAVIOR_PRESET_GROUP = 'behaviorPresetGroup';

    case MONSTER_PRESET = 'monsterPreset';
    case WORLD_PRESET = 'worldConfigPreset';
    case PLAYER_PRESET = 'playerConfigPreset';
    case ITEM_PRESET = 'itemPreset';
}