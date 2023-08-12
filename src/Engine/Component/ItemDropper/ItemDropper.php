<?php

declare(strict_types=1);

namespace App\Engine\Component\ItemDropper;

use App\Engine\Component\ComponentInterface;
use App\System\Item\ItemBlueprint;

class ItemDropper implements ComponentInterface
{
    public function __construct(
        private readonly ItemBlueprint $itemBlueprint,
        private readonly DropOn $dropOn,
        private readonly int $minAmount,
        private readonly int $maxAmount,
        private readonly float $chance,
    ) {
    }

    public function getItemBlueprint(): ItemBlueprint
    {
        return $this->itemBlueprint;
    }

    public function getDropOn(): DropOn
    {
        return $this->dropOn;
    }

    public function getMinAmount(): int
    {
        return $this->minAmount;
    }

    public function getMaxAmount(): int
    {
        return $this->maxAmount;
    }

    public function getChance(): float
    {
        return $this->chance;
    }
}
