<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Commands\MoveEntity;

class MovementQueue implements ActionQueueComponentInterface
{
    private ?int $msLastMovement;

    public function __construct(private readonly float $baseMovementSpeed)
    {
        $this->msLastMovement = null;
    }

    /** @var MoveEntity[]  */
    private array $movementQueue = [];

    public function add(MoveEntity $moveEntity): void
    {
        $this->movementQueue[] = $moveEntity;
    }

    public function dequeue(): ?MoveEntity
    {
        $v = array_shift($this->movementQueue);
        return $v;
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

    public function isQueueEmpty(): bool
    {
        return empty($this->movementQueue);
    }

    public function getBaseMovementSpeed(): float
    {
        return $this->baseMovementSpeed;
    }

    public function getMsLastMovement(): ?int
    {
        return $this->msLastMovement;
    }

    public function setMsLastMovement(int $msLastMovement): self
    {
        $this->msLastMovement = $msLastMovement;
        return $this;
    }
}
