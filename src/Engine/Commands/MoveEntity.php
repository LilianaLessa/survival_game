<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\System\Helpers\Point2D;

class MoveEntity
{
    public function __construct(
        private readonly Point2D $coordinates
    ) {
    }

    public function getCoordinates(): Point2D
    {
        return $this->coordinates;
    }
}
