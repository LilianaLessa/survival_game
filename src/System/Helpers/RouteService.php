<?php

declare(strict_types=1);

namespace App\System\Helpers;

use App\System\World\WorldManager;
use BlackScorp\Astar\Astar;
use BlackScorp\Astar\Grid;

class RouteService
{
    public function __construct(
        private readonly WorldManager $worldManager,
    ){
    }

    /**
     * @return Point2D[]
     */
    public function calculateRoute(Point2D $start, Point2D $end): array
    {
        $grid = new Grid($this->worldManager->getPathGroundWeights());

        $startPosition = $grid->getPoint($start->getY(), $start->getX());
        $endPosition = $grid->getPoint($end->getY(), $end->getX());
        $aStar = new Astar($grid);

        $nodes = $aStar->search($startPosition, $endPosition);
        array_shift($nodes);

        return array_map(fn ($n) => new Point2D($n->getX(), $n->getY()), $nodes);
    }
}
