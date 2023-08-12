<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\MapPosition;
use App\Engine\Component\Tree;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\WorldAwareTrait;
use App\System\World;

class TreeSpawner implements WorldSystemInterface
{
    use WorldAwareTrait;

    public function __construct(
        private readonly World $world,
        private readonly EntityManager $entityManager,
        private readonly int $maxTrees
    ) {
    }

    /** @param Entity[] $entityCollection */
    public function process(): void
    {
        //select randomly a tree in the map. if cant find any, select a random empty spot.
        //validate if this random spot have at least 1 free direction around. 8 directions.
        //from selected point spawn a new tree with 15% of chance on a random direction, if number of tree is < MAX_TREES
        $treesOnMap = $this->entityManager->getEntitiesWithComponents(
            MapPosition::class,
            Tree::class
        );
        if (count($treesOnMap) < $this->maxTrees) {
            $randomTreeId = array_keys($treesOnMap)[rand(0,count($treesOnMap)-1)] ?? null;

            //chance to pick a random location.
            $randomTreeId = rand(1,100) < 50 ? null : $randomTreeId;

            [ $basePosition ] = $treesOnMap[$randomTreeId] ?? [new MapPosition(
                rand(0, $this->world->getWidth()),
                rand(0, $this->world->getHeight()),
            )];

            $spawnPosition = new MapPosition(
                min($this->world->getWidth(), max(0, $basePosition->getX() + rand(-1,1))),
                min($this->world->getHeight(), max(0, $basePosition->getY() + rand(-1,1))),
            );

            if ($this->canOverlapOnWorld($spawnPosition->getX(), $spawnPosition->getY()) && rand(1,100) < 30) {
                Tree::createTree(
                    $this->entityManager,
                    $spawnPosition->getX(),
                    $spawnPosition->getY()
                );
            }
        }
    }
}
