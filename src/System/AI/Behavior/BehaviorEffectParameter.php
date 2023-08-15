<?php

declare(strict_types=1);

namespace App\System\AI\Behavior;

class BehaviorEffectParameter
{
    public function __construct(
        private readonly string $name,
        private readonly mixed $value,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
