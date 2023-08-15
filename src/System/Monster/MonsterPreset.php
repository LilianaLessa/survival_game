<?php

declare(strict_types=1);

namespace App\System\Monster;

use App\Engine\Component\BehaviorCollection;
use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\PresetDataType;

class MonsterPreset extends AbstractPreset
{
    public function __construct(
        string $name,
        private readonly ?string $symbol,
        //todo this is the quickest way to do this, as the data structure is the same,
        //     but it seems the layers are not being respected.
        private readonly BehaviorCollection $behaviorCollection,
    ) {
        parent::__construct(PresetDataType::MONSTER_PRESET, $name);
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