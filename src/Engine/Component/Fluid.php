<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\System\ForceDirection;

class Fluid implements DrawableInterface
{
    public function __construct(private readonly ForceDirection $forceDirection, private readonly int $strength)
    {
    }

    public function getForceDirection(): ForceDirection
    {
        return $this->forceDirection;
    }

    public function getStrength(): int
    {
        return $this->strength;
    }

    public function getSymbol(): string
    {
        return $this->forceDirection->value;
    }
}
