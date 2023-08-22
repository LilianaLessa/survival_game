<?php

declare(strict_types=1);

namespace App\System\Biome;

use App\System\Helpers\PerlinNoiseGenerator;
use App\System\Helpers\PerlinNoiseWave;
use App\System\Helpers\Point2D;
use App\System\World\WorldManager;

//todo kind of map generator, actually.
class BiomeGeneratorService
{
    public function __construct(
        private readonly BiomePresetLibrary $biomePresetLibrary,
        private readonly WorldManager $worldManager,
        private readonly PerlinNoiseGenerator $perlinNoiseGenerator,
    )
    {
    }

    public function generate(): array
    {
        $width = $this->worldManager->getWidth();
        $height = $this->worldManager->getHeight();
        $scale = 1.0;
        $offset = new Point2D(0,0);

        $biomePresets = $this->biomePresetLibrary->getAllGenerationEnabled();

        $waves = $this->getWaves();

        $maps = [];

        foreach ($waves as $waveType => $waveCollection) {
            $maps[$waveType] = $this->perlinNoiseGenerator->generateMap2D(
                $width,
                $height,
                $scale,
                $offset,
                ...$waveCollection,
            );
        }

        $mapBiomeData = [];

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $mapBiomeData[$x][$y] = [];

                foreach ($maps as $waveType => $map) {
                    $mapBiomeData[$x][$y][$waveType] = $map[$x][$y];
                }

                $mapBiomeData[$x][$y]['preset'] = $this->getSuitablePreset($mapBiomeData[$x][$y], ...$biomePresets);
            }
        }

        return $mapBiomeData;
    }
    private function getWaves(): array
    {
        $waves = [
            'height' => [
                new PerlinNoiseWave(
                    56,
                    0.05,
                    1,
                ),
                new PerlinNoiseWave(
                    199.36,
                    0.1,
                    0.5,
                ),
            ],
            'moisture' => [
                new PerlinNoiseWave(
                    621,
                    0.03,
                    1,
                ),
            ],
            'heat' => [
                new PerlinNoiseWave(
                    318.6,
                    0.04,
                    1,
                ),
                new PerlinNoiseWave(
                    329.7,
                    0.02,
                    5,
                ),
            ],
        ];

        return $waves;
    }

    private function getSuitablePreset(array $mapCelBiomeData, BiomePreset ...$biomePresets): ?BiomePreset
    {
        $possibleBiomes = [];

        foreach ($biomePresets as $biomePreset) {
            if ($biomePreset->matchCondition(...$mapCelBiomeData)) {
                $possibleBiomes[] = $biomePreset;
            }
        }

        $curVal = 0;
        $selectedBiome = null;
        foreach ($possibleBiomes as $possibleBiome) {
            if (!$selectedBiome || $possibleBiome->getDiffValue(...$mapCelBiomeData) < $curVal) {
                $selectedBiome = $possibleBiome;
                $curVal = $possibleBiome->getDiffValue(...$mapCelBiomeData);
            }
        }

        return $selectedBiome ?? $biomePresets[0] ?? null;
    }

}
