<?php

declare(strict_types=1);

namespace App\System\AI;

enum BehaviorAiConfigType: string
{
    case PRESET = 'preset';
    case PRESET_GROUP = 'presetGroup';
}
