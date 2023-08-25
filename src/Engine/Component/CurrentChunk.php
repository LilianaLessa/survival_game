<?php

declare(strict_types=1);

namespace App\Engine\Component;

class CurrentChunk implements ComponentInterface
{
    public function __construct(private readonly int $id)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }
}
