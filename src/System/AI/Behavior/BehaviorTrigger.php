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

    //todo this should support Symfony formula syntax
    //    so, there could be something like triggerType: between(1,2)
    public function getValue(): mixed
    {
        return $this->value;
    }
}
