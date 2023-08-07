<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\System\Direction;

class MoveEntity implements CommandInterface
{
    public function __construct(private readonly Direction $direction)
    {
    }

    public function execute(): void
    {
        // TODO: Implement execute() method.
    }

    public function getDirection(): Direction
    {
        return $this->direction;
    }
}
