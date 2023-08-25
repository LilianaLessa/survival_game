<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\System\Helpers\Dimension2D;
use App\System\Helpers\Point2D;

class MapViewPort implements ComponentInterface
{
    public function __construct(private readonly int $width, private readonly int $height)
    {
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function isPointInBoundaries(
        Point2D $point,
        MapPosition $basePosition,
        Dimension2D $mapDimension,
    ): bool
    {
        [$viewportStart, $viewportEnd] = $this->getBoundaries(
            $basePosition,
            $mapDimension
        );

        $x = $point->getX();
        $y = $point->getY();

        return !(
            $x < $viewportStart->getX()
            || $x > $viewportEnd->getX()
            || $y < $viewportStart->getY()
            || $y > $viewportEnd->getY()
        );
    }

    /** @return Point2D[] */
    public function getBoundaries(
        MapPosition $basePosition,
        Dimension2D $mapDimension,
    ): array {
        $viewportCenter = $this->calculateViewportCenter($basePosition, $mapDimension);

        return [
            new Point2D(
                (int) max(0, floor($viewportCenter->getX() - ($this->width / 2))),
                (int) max(0, floor($viewportCenter->getY() - ($this->height / 2))),
            ),
            new Point2D(
                (int)floor($viewportCenter->getX() + ($this->width / 2)),
                (int)floor($viewportCenter->getY() + ($this->height / 2)),
            )
        ];
    }

    private function calculateViewportCenter(
        ?MapPosition $basePosition,
        Dimension2D $mapDimension,
    ): Point2D {
        $mapWidth = $mapDimension->getWidth();
        $mapHeight = $mapDimension->getHeight();

        $viewportCenter = $basePosition?->get() ?? new Point2D($mapWidth / 2, $mapHeight / 2);

        $viewportCenterX = $viewportCenter->getX() - ($this->width / 2) < 0 ?
            ($this->width / 2) : $viewportCenter->getX();

        $viewportCenterX = $viewportCenterX + ($this->width / 2) >= $mapWidth - 1 ?
            $mapWidth - ($this->width / 2) - 1 : $viewportCenterX;

        $viewPortCenterY =
            $viewportCenter->getY() - ($this->height / 2) < 0 ?
                ($this->height) : $viewportCenter->getY();

        $viewPortCenterY =
            $viewPortCenterY + ($this->height / 2) >= $mapHeight - 1 ?
                $mapHeight - ($this->height / 2) - 1 : $viewPortCenterY;

        return new Point2D($viewportCenterX, $viewPortCenterY);
    }
}
