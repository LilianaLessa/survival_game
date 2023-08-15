<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers;

enum BehaviorTriggerType: string
{
    case ARE_ACTION_QUEUES_EMPTY = "areActionQueuesEmpty";

    case MS_TIME_FROM_LAST_BEHAVIOR_ACTIVATION = "msTimeFromLastBehaviorActivation";
}
