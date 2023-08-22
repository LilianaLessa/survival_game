<?php

declare(strict_types=1);

namespace App\System\Biome;

use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\PresetDataType;

class BiomePreset extends AbstractPreset
{
    /** @var string[]  */
    private array $colors;

    public function __construct(
        string $name,
        private readonly float $minHeight,
        private readonly float $minMoisture,
        private readonly float $minHeat,
        string ...$colors
    ) {

        parent::__construct(PresetDataType::BIOME_PRESET, $name);

        $this->colors = $colors;
    }

    public function getColors(): array
    {
        return $this->colors;
    }

    public function getMinHeight(): float
    {
        return $this->minHeight;
    }

    public function getMinMoisture(): float
    {
        return $this->minMoisture;
    }

    public function getMinHeat(): float
    {
        return $this->minHeat;
    }

    public function matchCondition(float $height, float $moisture, float $heat): bool
    {
        return $height >= $this->minHeight && $moisture >= $this->minMoisture && $heat >= $this->minHeat;
    }

    public function getDiffValue(float $height, float $moisture, float $heat): float
    {
        return ($height - $this->minHeight) + ($moisture - $this->minMoisture) + ($heat - $this->minHeat);
    }
}
