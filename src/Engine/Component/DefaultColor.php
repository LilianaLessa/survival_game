<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\System\Helpers\ConsoleColorPalette;

readonly class DefaultColor implements ComponentInterface
{

    public function __construct(
        private readonly ConsoleColorPalette $color,
    )
    {
    }

    public function getColor(): ConsoleColorPalette
    {
        return $this->color;
    }
}
