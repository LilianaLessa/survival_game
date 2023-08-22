<?php

declare(strict_types=1);

namespace App\Engine\Component;

readonly class DefaultColor implements ComponentInterface
{

    public function __construct(
        private string $color,
    )
    {
    }


    public function getColor(): string
    {
        return $this->color;
    }
}
