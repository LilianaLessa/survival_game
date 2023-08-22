<?php

declare(strict_types=1);

namespace App\System\Helpers;

class PerlinNoiseWave
{
    public function __construct(
        public readonly float $seed,
        public readonly float $frequency,
        public readonly float $amplitude,
    )
    {
    }
}
