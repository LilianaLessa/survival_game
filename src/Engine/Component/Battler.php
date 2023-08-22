<?php

declare(strict_types=1);

namespace App\Engine\Component;

readonly class Battler implements ComponentInterface
{
    public function __construct(private float $baseAttackSpeed)
    {
    }

    public function getBaseAttackSpeed(): float
    {
        return $this->baseAttackSpeed;
    }
}
