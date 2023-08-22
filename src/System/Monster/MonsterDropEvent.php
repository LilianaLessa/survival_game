<?php

declare(strict_types=1);

namespace App\System\Monster;

use App\Engine\Component\Item\ItemDropper\DropOn;

class MonsterDropEvent
{
    public function __construct(
        private readonly DropOn $dropOn,
        private readonly float $chance,
        private readonly int $minAmount,
        private readonly int $maxAmount
    )
    {
    }

    public function getDropOn(): DropOn
    {
        return $this->dropOn;
    }

    public function getChance(): float
    {
        return $this->chance;
    }

    public function getMinAmount(): int
    {
        return $this->minAmount;
    }

    public function getMaxAmount(): int
    {
        return $this->maxAmount;
    }
}
