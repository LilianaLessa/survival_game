<?php

declare(strict_types=1);

namespace App\System\Monster\Spawner;

use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\PresetDataType;

class MonsterSpawnerPreset extends AbstractPreset
{
    /* @var string[] */
    private array $biomes;


    public function __construct(
        string $name,
        private readonly string $monsterPresetName,
        private readonly int $maxAmount,
        private readonly float $chance,
        string ...$biomes,
    )
    {
        parent::__construct(PresetDataType::MONSTER_SPAWNER, $name);

        $this->biomes = $biomes;
    }

    public function getMonsterPresetName(): string
    {
        return $this->monsterPresetName;
    }

    public function getMaxAmount(): int
    {
        return $this->maxAmount;
    }

    public function getChance(): float
    {
        return $this->chance;
    }

    /**
     * @return string[]
     */
    public function getBiomes(): array
    {
        return $this->biomes;
    }
}
