<?php

declare(strict_types=1);

namespace App\Engine\Component;

class ColorEffect implements \App\Engine\Component\ComponentInterface
{
    private int $creationTime;

    public function __construct(
        private readonly int $lifeSpanInMs,
        private readonly string $color,
    )
    {
        $this->creationTime = (int) floor(microtime(true) * 1000);
    }

    public function isExpired(): bool
    {
        $current = (int) floor(microtime(true) * 1000);

        return $current - $this->creationTime >= $this->lifeSpanInMs;
    }

    public function getColor(): string
    {
        return $this->color;
    }
}
