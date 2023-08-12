<?php

declare(strict_types=1);

namespace App\Engine\Component;


use App\Engine\Commands\WorldAction;

class WorldActor implements ComponentInterface
{
    /** @var WorldAction[] */
    private array $actionQueue = [];


    public function addToQueue(WorldAction ...$actions): void
    {
        array_push($this->actionQueue, ...$actions);
    }

    /**
     * @return WorldAction[]
     */
    public function getActionQueue(): array
    {
        return $this->actionQueue;
    }

    public function clear()
    {
        foreach ($this->actionQueue as $i => &$m) {
            $m = null;
            unset($m);
            unset($this->actionQueue[$i]);
        }

        $this->actionQueue = [];
    }
}
