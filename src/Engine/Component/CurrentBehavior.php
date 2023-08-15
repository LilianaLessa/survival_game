<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\System\AI\Behavior\BehaviorPreset;

class CurrentBehavior implements ComponentInterface
{
    public function __construct(private readonly BehaviorPreset $behaviorPreset)
    {
    }

    public function getBehaviorPreset(): BehaviorPreset
    {
        return $this->behaviorPreset;
    }
}
