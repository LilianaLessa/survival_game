<?php

declare(strict_types=1);

namespace App\Engine\Component;

interface DrawableInterface extends ComponentInterface
{
    public function getSymbol(): string;
}
