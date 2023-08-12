<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Commands\MoveEntity;

class Movable implements ComponentInterface
{
    /** @var MoveEntity[]  */
    private array $movementQueue = [];

    public function add(MoveEntity $moveEntity): void
    {
        $this->movementQueue[] = $moveEntity;
    }

    public function getMovementQueue(): array
    {
        return $this->movementQueue;
    }

    public function clear()
    {
        foreach ($this->movementQueue as $i => &$m) {
            $m = null;
            unset($m);
            unset($this->movementQueue[$i]);
        }

        $this->movementQueue = [];
    }
}
