<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers\Move\Parameters;

use App\Engine\Component\ParentSpawner;
use App\Engine\Entity\Entity;
use App\System\Biome\BiomePreset;
use App\System\Helpers\Point2D;
use App\System\World\WorldManager;

class  RandomTargetBiomaCoordinates extends RandomTargetCoordinates
{
    public function __construct(
        protected readonly WorldManager $worldManager,
        protected readonly int $mapWidth,
        protected readonly int $mapHeight,
        protected readonly int $minDistance,
        protected readonly int $maxDistance
    ) {
    }

    public function getTargetPoint(Point2D $from, Entity $targetEntity): Point2D
    {
        /** @var ParentSpawner $parentSpawner */
        $parentSpawner = $targetEntity->getComponent(ParentSpawner::class);

        if (!$parentSpawner) {
            return parent::getTargetPoint($from, $targetEntity);
        }

        $spawnerBiomeNames = $parentSpawner->getParentSpawner()->getBiomes();

        $optionList = [];

        for($currentRadius = $this->minDistance; $currentRadius <= $this->maxDistance; $currentRadius++) {
            $surroundings =$this->getSurroundings($from, $currentRadius);

            foreach ($surroundings as $candidate) {
                /** @var BiomePreset $candidateBiome */
                $candidateBiome = $this->worldManager->getTerrainData()[$candidate[0]][$candidate[1]]['preset'] ?? null;

                $candidateBiomeName = $candidateBiome?->getName() ?? null;

                if (!$this->isOutOfBounds(...$candidate) && in_array($candidateBiomeName, $spawnerBiomeNames)) {
                    $optionList[] = $candidate;
                }
            }
        }

        if (empty($optionList)) {
            $optionList[] = $from->toArray();
        }

        return new Point2D(...$optionList[array_rand($optionList)]);
    }
}
