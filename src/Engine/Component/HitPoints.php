<?php

declare(strict_types=1);

namespace App\Engine\Component;

class HitPoints implements ComponentInterface
{
    public function __construct(private readonly int $current, private readonly int $total)
    {
    }

    public function getCurrent(): int
    {
        return $this->current;
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}
