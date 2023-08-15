<?php

declare(strict_types=1);

namespace App\System\Helpers;

class Point2D
{
    public function __construct(
        private readonly int $x,
        private readonly int $y
    )
    {
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function toArray(): array
    {
        return [
            $this->getX(),
            $this->getY(),
        ];
    }
}
