<?php

declare(strict_types=1);

namespace App\Engine\Component;

class MapSymbol implements DrawableInterface
{
    public function __construct(private readonly string $symbol)
    {
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }
}
