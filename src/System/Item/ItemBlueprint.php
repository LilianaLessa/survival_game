<?php

declare(strict_types=1);

namespace App\System\Item;

class ItemBlueprint
{
    public function __construct(
        private readonly string $id,
        private readonly string $internalName,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getInternalName(): string
    {
        return $this->internalName;
    }
}
