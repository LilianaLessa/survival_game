<?php

declare(strict_types=1);

namespace App\Engine\Component\Item\ItemDropper;

use App\Engine\Component\ComponentInterface;
use App\System\Item\ItemPreset;

class ItemDropper implements ComponentInterface
{
    public function __construct(
        private readonly ItemPreset $itemBlueprint,
        private readonly DropOn $dropOn,
        private readonly int $minAmount,
        private readonly int $maxAmount,
        private readonly float $chance,
    ) {
    }

    public function getItemBlueprint(): ItemPreset
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
