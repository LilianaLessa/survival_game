<?php

declare(strict_types=1);

namespace App\System\Helpers;

class Dimension2D
{
    public function __construct(
        private readonly int $width,
        private readonly int $height,
    )
    {
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function isOutOfBounds(Point2D $p): bool
    {
        $x = $p->getX();
        $y = $p->getY();

        return
            $x < 0
            || $x > $this->width -1
            || $y < 0
            || $y > $this->height -1
        ;
    }
}
