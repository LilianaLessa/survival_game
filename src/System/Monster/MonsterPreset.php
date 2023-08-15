<?php

declare(strict_types=1);

namespace App\System\Monster;

use App\Engine\Component\BehaviorCollection;

class MonsterPreset
{
    public function __construct(
        private readonly string $name,
        private readonly ?string $symbol,
        //todo this is the quickest way to do this, as the data structure is the same,
        //     but it seems the layers are not being respected.
        private readonly BehaviorCollection $behaviorCollection,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getBehaviorCollection(): BehaviorCollection
    {
        return $this->behaviorCollection;
    }
}
