<?php

declare(strict_types=1);

namespace App\System;

use App\Engine\Component\DrawableInterface;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Component\Player;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityCollection;
use App\Engine\Entity\EntityManager;

class World
{
    private const EMPTY_CELL_SYMBOL = '.';

    /** @var EntityCollection[][]  */
    private array $entityMap = [];

    private string $drawableClass = MapSymbol::class;

    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly int $width,
        private readonly int $height,
        //TODO the viewport should also be a component on an entity, that can be attached to a socket.
        // this way, it's possible to create multiple map clients
        // with each having it's own viewport, attached to the player entity.
        private int $viewportWidth,
        private int $viewportHeight,
    )
    {
        $this->resetEntityMap();
    }

    public function resetEntityMap(): void
    {
        foreach ($this->entityMap as $x => $entityRow) {
            foreach ($entityRow as $y => $entity) {
                unset($this->entityMap[$x][$y]);
            }
        }
    }

    /**
     * @param EntityCollection[][] $entityMap
     */
    public function setEntityMap(array $entityMap): void
    {
        $this->entityMap = $entityMap;
    }

    /**
     * @return EntityCollection[][]
     */
    public function getEntityMap(): array
    {
        return $this->entityMap;
    }

    public function draw(): void
    {
        [$viewportStart, $viewportEnd] =$this->calculateViewport();



        //show world x coordinates on viewport border.
        for($i = 0; $i < strlen($this->width . ''); $i++) {
            echo str_pad(
                '#',
                strlen((string)$this->height),
                '#',
                STR_PAD_LEFT
            );

            for ($mapX = $viewportStart['x']; $mapX <= $viewportEnd['x']; $mapX++) {
                $paddedMapX = str_split(str_pad(
                    (string) $mapX,
                    strlen((string)$this->width),
                    '0',
                    STR_PAD_LEFT
                ));

                echo ' ' . $paddedMapX[$i];
            }
            echo "\n";
        }

        for ($mapY = $viewportStart['y']; $mapY <= $viewportEnd['y']; $mapY++) {

            //show world y coordinates on viewport border.
            echo str_pad(
                (string) $mapY,
                strlen((string)$this->height),
                '0',
                STR_PAD_LEFT
            );

            for ($mapX = $viewportStart['x']; $mapX <= $viewportEnd['x']; $mapX++) {

                $entities = $this->entityMap[$mapX][$mapY] ?? new EntityCollection();
                $drawableEntities = $entities->getEntitiesWithComponents(
                    $this->drawableClass
                );

                /* @var bool|Entity $topEntity */
                $topEntity = end($drawableEntities);

                /** @var null|DrawableInterface */
                [ $drawable ] = $topEntity ? $topEntity : [ null ];

                $symbol = $drawable?->getSymbol() ?? self::EMPTY_CELL_SYMBOL;

                echo sprintf(" %s", $symbol);
            }
            echo "\n";
        }
    }

    public function getEntityCollection(int $x, int $y): EntityCollection
    {
        return $this->entityMap[$x][$y] ?? new EntityCollection();
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function isOutOfBounds(int $x, int $y): bool
    {
        return
        $x < 0
        || $x > $this->getWidth()
        || $y < 0
        || $y > $this->getHeight();
    }

    public function setDrawableClass(?string $drawableClass): void
    {
        $this->drawableClass =
            ($drawableClass && in_array(DrawableInterface::class, class_implements($drawableClass))
            ? $drawableClass : MapSymbol::class);
    }

    public function getViewportWidth(): int
    {
        return $this->viewportWidth;
    }

    public function setViewportWidth(int $viewportWidth): self
    {
        $this->viewportWidth = $viewportWidth;
        return $this;
    }

    public function getViewportHeight(): int
    {
        return $this->viewportHeight;
    }

    public function setViewportHeight(int $viewportHeight): self
    {
        $this->viewportHeight = $viewportHeight;
        return $this;
    }

    private function calculateViewport(): array
    {
        $player = $this->entityManager->getEntitiesWithComponents(MapPosition::class, Player::class);

        /** @var ?MapPosition $playerMapPosition */
        [$playerMapPosition] = array_shift($player) ?? [null];

        $viewPortCenter = [
            'x' => floor($playerMapPosition?->getX() ?? $this->width / 2),
            'y' => floor($playerMapPosition?->getY() ?? $this->height / 2),
        ];

        $viewPortCenter['x'] =
            $viewPortCenter['x'] - ($this->viewportWidth / 2) < 0 ?
                ($this->viewportWidth / 2) : $viewPortCenter['x'];

        $viewPortCenter['x'] =
            $viewPortCenter['x'] + ($this->viewportWidth / 2) >= $this->width -1 ?
                $this->width - ($this->viewportWidth / 2) - 1 : $viewPortCenter['x'];

        $viewPortCenter['y'] =
            $viewPortCenter['y'] - ($this->viewportHeight / 2) < 0 ?
                ($this->viewportHeight / 2) : $viewPortCenter['y'];

        $viewPortCenter['y'] =
            $viewPortCenter['y'] + ($this->viewportHeight / 2) >= $this->height - 1?
                $this->height - ($this->viewportHeight / 2) - 1: $viewPortCenter['y'];

        $viewportStart = [
            'x' => max(0, floor($viewPortCenter['x'] - ($this->viewportWidth / 2))),
            'y' => max(0, floor($viewPortCenter['y'] - ($this->viewportHeight / 2))),
        ];

        $viewportEnd = [
            'x' => floor($viewPortCenter['x'] + ($this->viewportWidth / 2)),
            'y' => floor($viewPortCenter['y'] + ($this->viewportHeight / 2)),
        ];

        return [
            $viewportStart,
            $viewportEnd
        ];
    }
}
