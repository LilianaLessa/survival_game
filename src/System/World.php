<?php

declare(strict_types=1);

namespace App\System;

use App\Engine\Component\DrawableInterface;
use App\Engine\Component\MapSymbol;
use App\Engine\Entity\Entity;

class World
{
    private const EMPTY_CELL_SYMBOL = '.';

    // TODO implement viewport/scrollable world?

    /** TODO This should be a bi-dimensional entity matrix */
    /** @var Entity[][][]  */
    private array $entityMap = [];

    private string $drawableClass = MapSymbol::class;

    public function __construct(private readonly int $width, private readonly int $height)
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
     * @param Entity[][][] $entityMap
     */
    public function setEntityMap(array $entityMap): void
    {
        $this->entityMap = $entityMap;
    }

    public function draw(): void
    {
        for ($h = 0; $h < $this->height; $h++) {
            for ($w = 0; $w < $this->width; $w++) {
                $entities = $this->entityMap[$w][$h] ?? [];
                /* @var bool|Entity $topEntity */
                $topEntity = end($entities);
                /* @var null|MapSymbol $symbol */
                $symbol = $topEntity ? $topEntity->getComponent($this->drawableClass) : null;
                $symbol = $symbol?->getSymbol() ?? self::EMPTY_CELL_SYMBOL;

                echo sprintf(" %s ", $symbol);

            }
            echo "\n";
        }
    }

    public function getEntityCollection(int $x, int $y): array
    {
        return $this->entityMap[$x][$y] ?? [];
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
        || $x > $this->world->getWidth()
        || $y < 0
        || $y > $this->world->getHeight();
    }

    public function setDrawableClass(?string $drawableClass): void
    {
        $this->drawableClass =
            ($drawableClass && in_array(DrawableInterface::class, class_implements($drawableClass))
            ? $drawableClass : MapSymbol::class);
    }
}
