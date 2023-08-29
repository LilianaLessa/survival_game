<?php

declare(strict_types=1);

namespace App\System\PresetLibrary;

enum PresetDataType: string
{
    case BEHAVIOR_PRESET = 'behaviorPreset';
    case BEHAVIOR_PRESET_GROUP = 'behaviorPresetGroup';

    case MONSTER_PRESET = 'monsterPreset';
    case MONSTER_SPAWNER = 'monsterSpawner';
    case WORLD_PRESET = 'worldConfigPreset';
    case PLAYER_PRESET = 'playerConfigPreset';
    case ITEM_PRESET = 'itemPreset';
    case BIOME_PRESET = 'biomePreset';
    case SERVER_CONFIG = 'serverConfig';
    case LIBRARY_CONFIG = 'libraryConfig';
}
