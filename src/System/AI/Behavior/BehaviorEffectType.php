<?php

declare(strict_types=1);

namespace App\System\AI\Behavior;

enum BehaviorEffectType: string
{
    case MOVE = "move";
    case INCREASE_AGGRO = "increaseAggro";
    case ATTACK = 'attack';
}
