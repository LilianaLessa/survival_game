<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers\Move\Parameters;

use App\Engine\Entity\Entity;
use App\System\Helpers\Point2D;

class  RandomTargetCoordinates implements TargetCoordinatesInterface
{
    public function __construct(
        protected readonly int $mapWidth,
        protected readonly int $mapHeight,
        protected readonly int $minDistance,
        protected readonly int $maxDistance
    ) {
    }

    public function getTargetPoint(Point2D $from, Entity $targetEntity): Point2D
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

    protected function getSurroundings(Point2D $from, int $currentRadius): array
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

    protected function isOutOfBounds(int $x, int $y): bool
    {
        return
            $x < 0
            || $x > $this->mapWidth -1
            || $y < 0
            || $y > $this->mapHeight -1;
    }
}
