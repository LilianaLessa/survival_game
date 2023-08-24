<?php

declare(strict_types=1);

namespace App\Engine\Component;

class InGameName implements ComponentInterface
{
    public function __construct(private readonly string $inGameName)
    {
    }

    public function getInGameName(): string
    {
        return $this->inGameName;
    }
}

