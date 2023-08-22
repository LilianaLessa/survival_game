<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers\IncreaseAggro\Parameters;

enum AggroTargetType: string
{
    case TRIGGER_ENTITY = 'triggerEntity';
}
