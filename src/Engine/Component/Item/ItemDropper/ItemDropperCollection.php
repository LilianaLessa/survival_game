<?php

declare(strict_types=1);

namespace App\Engine\Component\Item\ItemDropper;

use App\Engine\Component\ComponentInterface;

class ItemDropperCollection implements ComponentInterface
{
    /** @var ItemDropper[] */
    private array $itemDroppers;

    public function __construct(ItemDropper ...$itemDroppers)
    {
        $this->itemDroppers = $itemDroppers;
    }

    public function getItemDroppers(): array
    {
        return $this->itemDroppers;
    }
}
