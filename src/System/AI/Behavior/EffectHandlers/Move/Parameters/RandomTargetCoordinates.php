<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers\Move\Parameters;

class  RandomTargetCoordinates implements TargetCoordinatesInterface
{
    public function __construct(
        private readonly int $mapWidth,
        private readonly int $mapHeight,
        private readonly int $minDistance,
        private readonly int $maxDistance
    ) {
    }

    public function getX(int $fromX): int
    {
        // TODO: Implement getX() method.
    }

    public function getY(int $fromY): int
    {
        // TODO: Implement getY() method.
    }
}
