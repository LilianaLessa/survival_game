<?php

declare(strict_types=1);

namespace App\System\AI\Behavior;

use App\System\AI\Behavior\EffectHandlers\BehaviorTriggerType;

class BehaviorTrigger
{
    public function __construct(
        private readonly BehaviorTriggerType $name,
        private readonly mixed $value,
    ) {
    }

    public function getName(): BehaviorTriggerType
    {
        return $this->name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
