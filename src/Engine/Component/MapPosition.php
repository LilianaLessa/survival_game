<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\System\Helpers\Point2D;

class MapPosition implements ComponentInterface
{
    public function __construct(private readonly int $x, private readonly int $y)
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

    public function get(): Point2D
    {
        return new Point2D($this->getX(), $this->getY());
    }
}
