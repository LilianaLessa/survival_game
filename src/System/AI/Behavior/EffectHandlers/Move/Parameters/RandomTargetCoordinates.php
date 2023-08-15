<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers\Move\Parameters;

use App\System\Helpers\Point2D;
use App\System\World;

class  RandomTargetCoordinates implements TargetCoordinatesInterface
{
    public function __construct(
        private readonly int $mapWidth,
        private readonly int $mapHeight,
        private readonly int $minDistance,
        private readonly int $maxDistance
    ) {
    }

    public function getTargetPoint(Point2D $from): Point2D
    {
        $optionList = [];

        for($currentRadius = $this->minDistance; $currentRadius <= $this->maxDistance; $currentRadius++) {
            $surroundings =$this->getSurroundings($from, $currentRadius);

            foreach ($surroundings as $candidate) {
                if (!$this->isOutOfBounds(...$candidate)) {
                    $optionList[] = $candidate;
                }
            }
        }

        if (empty($optionList)) {
            $optionList[] = $from->toArray();
        }

        return new Point2D(...$optionList[array_rand($optionList)]);
    }

    private function getSurroundings(Point2D $from, int $currentRadius): array
    {
        return [
            [
                $from->getX() + $currentRadius,
                $from->getY() + $currentRadius,
            ],
            [
                $from->getX() - $currentRadius,
                $from->getY() - $currentRadius,
            ],
            [
                $from->getX() + $currentRadius,
                $from->getY(),
            ],
            [
                $from->getX() - $currentRadius,
                $from->getY(),
            ],
            [
                $from->getX(),
                $from->getY() + $currentRadius,
            ],
            [
                $from->getX(),
                $from->getY() - $currentRadius,
            ],
            [
                $from->getX() - $currentRadius,
                $from->getY() + $currentRadius,
            ],
            [
                $from->getX() + $currentRadius,
                $from->getY() - $currentRadius,
            ],
        ];
    }

    public function isOutOfBounds(int $x, int $y): bool
    {
        return
            $x < 0
            || $x > $this->mapWidth -1
            || $y < 0
            || $y > $this->mapHeight -1;
    }
}
