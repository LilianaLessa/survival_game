<?php

declare(strict_types=1);

namespace App\System\World;

use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\PresetDataType;

class WorldPreset extends AbstractPreset
{
    private int $screenUpdaterFps = 10;

    public function __construct(
        string $name,
        private readonly int $mapWidth,
        private readonly int $mapHeight,
        private readonly int $chunkWidth,
        private readonly int $chunkHeight,
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

    public function getScreenUpdaterFps(): int
    {
        return $this->screenUpdaterFps;
    }

    public function setScreenUpdaterFps(int $screenUpdaterFps): self
    {
        $this->screenUpdaterFps = $screenUpdaterFps;
        return $this;
    }

    public function getChunkWidth(): int
    {
        return $this->chunkWidth;
    }

    public function getChunkHeight(): int
    {
        return $this->chunkHeight;
    }
}
