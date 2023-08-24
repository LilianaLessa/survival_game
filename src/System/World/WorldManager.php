<?php

declare(strict_types=1);

namespace App\System\World;

use App\Engine\Component\Collideable;
use App\Engine\Component\ColorEffect;
use App\Engine\Component\DefaultColor;
use App\Engine\Component\DrawableInterface;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Component\Player;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityCollection;
use App\Engine\Entity\EntityManager;
use App\System\Biome\BiomePreset;
use App\System\Helpers\ConsoleColorPalette;
use App\System\Helpers\Point2D;
use App\System\Player\PlayerPresetLibrary;
use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;

class WorldManager
{
    private const EMPTY_CELL_SYMBOL = ' ';

    /** @var EntityCollection[][] */
    private array $entityMap = [];

    private string $drawableClass = MapSymbol::class;
    private int $width;
    private int $height;
    private int $viewportWidth;
    private int $viewportHeight;
    private ?array $groundPathWeights = null;
    private $lastDraw = null;

    private $mapBiomeData = [];

    private $linearBiomeData = [];

    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly WorldPresetLibrary $worldPresetLibrary,
        //TODO the viewport should also be a component on an entity, that can be attached to a socket.
        // this way, it's possible to create multiple map clients
        // with each having it's own viewport, attached to the player entity.
        private readonly PlayerPresetLibrary $playerPresetLibrary,
        private readonly ConsoleColor $consoleColor,
    )
    {
        $worldPreset = $this->worldPresetLibrary->getDefaultWorldPreset();
        $this->width = $worldPreset->getMapWidth();
        $this->height = $worldPreset->getMapHeight();

        $playerPreset = $this->playerPresetLibrary->getDefaultPlayerPreset();

        $this->viewportWidth = $playerPreset->getInitialViewportWidth();
        $this->viewportHeight = $playerPreset->getInitialViewportHeight();

        $this->resetEntityMap();
        $this->groundPathWeights = $this->getPathGroundWeights();
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

    public function drawByBufferSwap(): void
    {
        $this->groundPathWeights = null;
        [$viewportStart, $viewportEnd] = $this->calculateViewport();

        ob_start();
        //show world x coordinates on viewport border.
        for ($i = 0; $i < strlen($this->width . ''); $i++) {
            echo str_pad(
                '#',
                strlen((string)$this->height),
                '#',
                STR_PAD_LEFT
            );

            for ($mapX = $viewportStart['x']; $mapX <= $viewportEnd['x']; $mapX++) {
                $paddedMapX = str_split(str_pad(
                    (string)$mapX,
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
                (string)$mapY,
                strlen((string)$this->height),
                '0',
                STR_PAD_LEFT
            );

            for ($mapX = $viewportStart['x']; $mapX <= $viewportEnd['x']; $mapX++) {

                $entities = $this->entityMap[$mapX][$mapY] ?? new EntityCollection();
                $drawableEntities = $entities->getEntitiesWithComponents(
                    $this->drawableClass
                );

                $entityIds = array_keys($drawableEntities);
                $topEntityId = end($entityIds);
                $topEntityId = $topEntityId ?: '';
                /* @var ?Entity $topEntity */
                $topEntity = $this->entityManager->getEntityById($topEntityId) ?? null;

                /** @var null|DrawableInterface */
                $drawable = $topEntity?->getComponent($this->drawableClass);

                $symbol = $drawable?->getSymbol() ?? self::EMPTY_CELL_SYMBOL;

                $symbol = sprintf(" %s", $symbol);

                echo $this->consoleColor->apply(
                    [
                        sprintf('bg_color_%d', $this->getBackgroudColor((int)$mapX, (int)$mapY)->toInt()),
                        sprintf('color_%d', $this->getForegroundColor($topEntity)->toInt()),
                    ],
                    $symbol
                );
            }
            echo "\n";
        }

        if ($this->lastDraw !== null) {
            echo sprintf(
                "\n%s\n%d\n",
                microtime(true) - $this->lastDraw,
                count($this->entityManager->getEntityCollection())
            );
        }

        //swap frame
        $frame = ob_get_contents();
        ob_end_clean();
        system('clear');
        echo $frame;

        $this->lastDraw = microtime(true);
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

    //todo fix it, as world size ends on dimension-1.
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
            $viewPortCenter['x'] + ($this->viewportWidth / 2) >= $this->width - 1 ?
                $this->width - ($this->viewportWidth / 2) - 1 : $viewPortCenter['x'];

        $viewPortCenter['y'] =
            $viewPortCenter['y'] - ($this->viewportHeight / 2) < 0 ?
                ($this->viewportHeight / 2) : $viewPortCenter['y'];

        $viewPortCenter['y'] =
            $viewPortCenter['y'] + ($this->viewportHeight / 2) >= $this->height - 1 ?
                $this->height - ($this->viewportHeight / 2) - 1 : $viewPortCenter['y'];

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

    /** float[][] */
    public function getPathGroundWeights(): array
    {
        if ($this->groundPathWeights == null) {
            $this->groundPathWeights = [];

            for ($mapY = 0; $mapY < $this->getHeight(); $mapY++){
                for ($mapX = 0; $mapX < $this->getWidth(); $mapX++){
                    $this->groundPathWeights[$mapX][$mapY] = $this->entityMap[$mapX][$mapY] ?? null
                        ? count($this->entityMap[$mapX][$mapY]
                            ->getEntitiesWithComponents(Collideable::class))
                        : 0;
                }
            }
        }

        return $this->groundPathWeights;
    }

    public function getMapBiomeData(): array
    {
        return $this->mapBiomeData;
    }

    public function setMapBiomeData(array $mapBiomeData): self
    {
        $this->linearBiomeData = [];

        foreach ($mapBiomeData as $x => $column) {
            foreach ($column as $y => $data) {
                $this->linearBiomeData[$data['preset']->getName()][] = new Point2D($x, $y);
            }
        }

        $this->mapBiomeData = $mapBiomeData;
        return $this;
    }

    /** @return Point2D[] */
    public function getLinearBiomeData(string $biomeName): array
    {
        return $this->linearBiomeData[$biomeName] ?? [];
    }

    private function getBackgroudColor(int $x, int $y): ConsoleColorPalette
    {
        $result = 'bg_default';

        /** @var ?BiomePreset $biome */
        $biome = $this->mapBiomeData[$x][$y]['preset'];

        if ($biome) {
            $colors = $biome->getColors();
            $randomColor = $colors[rand(0, count($colors)-1)];

            $result = ConsoleColorPalette::tryFrom($randomColor) ?? ConsoleColorPalette::default();
        }

        return $result;
    }

    private function getForegroundColor(?Entity $topEntity): ConsoleColorPalette
    {
        /** @var ?ColorEffect $colorEffect */
        $colorEffect = $topEntity ? $topEntity?->getComponent(ColorEffect::class) : null;
        $defaultColor = $topEntity ? $topEntity?->getComponent(DefaultColor::class) : null;

        return $colorEffect?->getColor()
        ?? $defaultColor?->getColor()
        ?? ConsoleColorPalette::default();
    }
}
