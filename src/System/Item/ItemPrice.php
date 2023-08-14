<?php

declare(strict_types=1);

namespace App\System\Item;

class ItemPrice
{
    public function __construct(
        private readonly int $c,
        private readonly int $s,
        private readonly int $g,
    ) {
    }

    public function getC(): int
    {
        return $this->c;
    }

    public function getS(): int
    {
        return $this->s;
    }

    public function getG(): int
    {
        return $this->g;
    }
}
