<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\System\Direction;

class MoveEntity implements CommandInterface
{
    //todo should different types of movement be declared here?
    // for example, climb, swim, walk, run and so on?
    public function __construct(
        private readonly Direction $direction
    ) {
    }

    public function getDirection(): Direction
    {
        return $this->direction;
    }
}
