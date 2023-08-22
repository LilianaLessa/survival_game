<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\System\Helpers\ConsoleColorPalette;

class ColorEffect implements ComponentInterface
{
    private int $creationTime;

    public function __construct(
        private readonly int                 $lifeSpanInMs,
        private readonly ConsoleColorPalette $color,
    )
    {
        $this->creationTime = (int) floor(microtime(true) * 1000);
    }

    public function isExpired(): bool
    {
        $current = (int) floor(microtime(true) * 1000);

        return $current - $this->creationTime >= $this->lifeSpanInMs;
    }

    public function getColor(): ConsoleColorPalette
    {
        return $this->color;
    }
}
