<?php

declare(strict_types=1);

namespace App\System\World;

use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\PresetDataType;

class WorldPreset extends AbstractPreset
{
    public function __construct(
        string $name,
        private readonly int $mapWidth,
        private readonly int $mapHeight,
    )
    {
        parent::__construct(PresetDataType::WORLD_PRESET, $name);
    }

    public function getMapWidth(): int
    {
        return $this->mapWidth;
    }

    public function getMapHeight(): int
    {
        return $this->mapHeight;
    }
}
