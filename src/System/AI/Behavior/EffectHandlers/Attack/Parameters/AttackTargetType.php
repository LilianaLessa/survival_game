<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers\Attack\Parameters;

enum AttackTargetType: string
{
    case TOP_AGGRO_ENTITY = 'topAggroEntity';
}
