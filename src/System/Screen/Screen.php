<?php

declare(strict_types=1);

namespace App\System\Screen;

use App\Engine\Component\ColorEffect;
use App\Engine\Component\DefaultColor;
use App\Engine\Component\DrawableInterface;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapViewPort;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityCollection;
use App\System\Helpers\ConsoleColorPalette;
use App\System\Helpers\Dimension2D;
use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;

class Screen
{
    private const EMPTY_CELL_SYMBOL = ' ';

    /** @var EntityCollection[][] */
    private array $entityMap = [];

    private Dimension2D $mapDimension;
    private Entity $viewer;
    private string $drawableClass;
    private EntityCollection $linearEntityCollection;
    private ?float $lastDraw = null;

    /** @var ConsoleColorPalette[][] */
    private array $backgroundColorMap;

    public function __construct(private readonly ConsoleColor $consoleColor)
    {
    }

    public function setScreenData(
        array $entityMap,
        array $backgroundColorMap,
        EntityCollection $linearEntityCollection,
        Dimension2D $mapDimension,
        Entity $viewer,
        string $drawableClass,
    ): self {
        $this->entityMap = $entityMap;
        $this->backgroundColorMap = $backgroundColorMap;
        $this->linearEntityCollection = $linearEntityCollection;
        $this->mapDimension = $mapDimension;
        $this->viewer = $viewer;
        $this->drawableClass = $drawableClass;

        return $this;
    }

    public function swapFrame(): void
    {
        $frame = $this->renderFrame();

        system('clear');
        echo $frame;

        $this->lastDraw = microtime(true);
    }

    private function renderFrame(): string
    {
        /** @var ?MapViewPort $baseViewport */
        /** @var ?MapPosition $basePosition */
        [$baseViewport, $basePosition] = $this->viewer->explode(
            MapViewPort::class,
            MapPosition::class,
        );

        [$viewportStart, $viewportEnd] = $baseViewport->getBoundaries(
            $basePosition,
            $this->mapDimension,
        );
        ob_start();

        $this->renderXCoordinatesOnTopBorder($viewportStart, $viewportEnd);

        for ($mapY = $viewportStart->getY(); $mapY <= $viewportEnd->getY(); $mapY++) {
            $this->renderYCoordinateOnLeftBorder($mapY);

            for ($mapX = $viewportStart->getX(); $mapX <= $viewportEnd->getX(); $mapX++) {
                $topEntity = $this->getTopDrawbleEntity($mapX, $mapY);
                $symbol = $this->getSymbolToDraw($topEntity);

                echo $this->consoleColor->apply(
                    [
                        sprintf('bg_color_%d', $this->getBackgroundColor((int)$mapX, (int)$mapY)->toInt()),
                        sprintf('color_%d', $this->getForegroundColor($topEntity)->toInt()),
                    ],
                    sprintf("%s ", $symbol)
                );
            }
            echo "\n";
        }

        $this->renderPerformanceInfo();
        $this->renderClientAndScreenIds();

        $frame = ob_get_contents();
        ob_end_clean();

        return $frame;
    }

    private function renderXCoordinatesOnTopBorder(mixed $viewportStart, mixed $viewportEnd): void
    {
        for ($i = 0; $i < strlen($this->mapDimension->getWidth() . ''); $i++) {
            echo str_pad(
                '#',
                strlen((string)$this->mapDimension->getHeight()),
                '#',
                STR_PAD_LEFT
            );

            for ($mapX = $viewportStart->getX(); $mapX <= $viewportEnd->getX(); $mapX++) {
                $paddedMapX = str_split(str_pad(
                    (string)$mapX,
                    strlen((string)$this->mapDimension->getWidth()),
                    '0',
                    STR_PAD_LEFT
                ));

                echo sprintf("%s ", $paddedMapX[$i]);
            }
            echo "\n";
        }
    }

    private function renderYCoordinateOnLeftBorder(int $mapY): void
    {
        echo str_pad(
            (string)$mapY,
            strlen((string)$this->mapDimension->getHeight()),
            '0',
            STR_PAD_LEFT
        );
    }

    private function getTopDrawbleEntity(int $mapX, int $mapY): ?Entity
    {
        $entities = $this->entityMap[$mapX][$mapY] ?? new EntityCollection();
        $drawableEntities = $entities->getEntitiesWithComponents($this->drawableClass);

        $entityIds = array_keys($drawableEntities);
        $topEntityId = end($entityIds);
        $topEntityId = $topEntityId ?: '';

        return $this->linearEntityCollection[$topEntityId] ?? null;
    }

    private function getSymbolToDraw(?Entity $topEntity): string
    {
        /** @var null|DrawableInterface */
        $drawable = $topEntity?->getComponent($this->drawableClass);

        return $drawable?->getSymbol() ?? self::EMPTY_CELL_SYMBOL;
    }

    private function getBackgroundColor(int $mapX, int $mapY): ConsoleColorPalette
    {
        return $this->backgroundColorMap[$mapX][$mapY] ?? ConsoleColorPalette::defaultBackground();
    }

    private function getForegroundColor(?Entity $entity): ConsoleColorPalette
    {
        /**
         * @var ColorEffect $colorEffect
         * @var DefaultColor $defaultColor
         */
        [
            $colorEffect,
            $defaultColor
        ] = $entity?->explode(
            ColorEffect::class,
            DefaultColor::class
        ) ?? [null, null];

        return $colorEffect?->getColor()
            ?? $defaultColor?->getColor()
            ?? ConsoleColorPalette::defaultForeground();
    }

    private function renderPerformanceInfo(): void
    {
        if ($this->lastDraw !== null) {
            echo sprintf(
                "\n%s\n%d\n",
                microtime(true) - $this->lastDraw,
                count($this->linearEntityCollection)
            );
        }
    }

    private function renderClientAndScreenIds():void
    {
        //todo draw client and screen id.
    }
}
