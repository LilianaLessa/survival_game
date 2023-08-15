<?php

declare(strict_types=1);

namespace App\System;

enum PresetDataType: string
{
    case BEHAVIOR_PRESET = 'behaviorPreset';
    case BEHAVIOR_PRESET_GROUP = 'behaviorPresetGroup';

    case MONSTER_PRESET = 'monsterPreset';
}
