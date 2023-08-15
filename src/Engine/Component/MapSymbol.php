<?php

declare(strict_types=1);

namespace App\Engine\Component;

//TODO when the engine evolves to support more complex graphic types, it would be better to change to Icon or something.
class MapSymbol implements DrawableInterface
{
    private const NULL_SYMBOL = '�';


    public function __construct(private readonly ?string $symbol)
    {
    }

    public function getSymbol(): string
    {
        return $this->symbol ?? self::NULL_SYMBOL;
    }
}
