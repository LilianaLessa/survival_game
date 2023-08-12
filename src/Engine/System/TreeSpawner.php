<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Commands\MoveEntity;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Component\Monster;
use App\Engine\Component\Tree;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\WorldAwareTrait;
use App\System\Direction;
use App\System\World;
use Cassandra\Map;

class TreeSpawner implements WorldSystemInterface
{
    use WorldAwareTrait;

    private const MAX_TREES = 10;

    public function __construct(private readonly World $world, private readonly EntityManager $entityManager)
    {
    }

    /** @param Entity[] $entityCollection */
    public function process(array $entityCollection): void
    {
        //select randomly a tree in the map. if cant find any, select a random empty spot.
        //validate if this random spot have at least 1 free direction around. 8 directions.
        //from selected point spawn a new tree with 15% of chance on a random direction, if number of tree is < MAX_TREES


        $treesOnMap =  array_values(array_filter($entityCollection, fn ($e) => $e->getComponent(Tree::class)));
        //$treesOnMap = $treesOnMap ? $treesOnMap : [];
        if (count($treesOnMap) < self::MAX_TREES) {

            $randomTree = $treesOnMap[rand(0,count($treesOnMap)-1)] ?? null;
            $basePosition = $randomTree?->getComponent(MapPosition::class) ?? new MapPosition(
                rand(0, $this->world->getWidth()),
                rand(0, $this->world->getHeight()),
            );

            $spawnPosition = new MapPosition(
                min($this->world->getWidth(), max(0, $basePosition->getX() + rand(-1,1))),
                min($this->world->getHeight(), max(0, $basePosition->getY() + rand(-1,1))),
            );

            if ($this->canOverlap($spawnPosition->getX(), $spawnPosition->getY()) && rand(1,100) < 15) {
                //spawn new tree on position;
                $this->entityManager->addEntity(
                    Tree::createTree(
                        time(),
                        $spawnPosition->getX(),
                        $spawnPosition->getY())
                );
            }
        }
    }
}
