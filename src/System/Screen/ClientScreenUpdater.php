<?php

declare(strict_types=1);

namespace App\System\Screen;

use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityCollection;
use App\System\Helpers\ConsoleColorPalette;
use App\System\Helpers\Dimension2D;
use App\System\Server\Client\MapClient;
use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;
use function Amp\async;
use function Amp\delay;

class ClientScreenUpdater
{
    private int $fps;

    public function __construct(private readonly Screen $screen)
    {
        //todo get from right preset.
        $this->fps = 30;
    }

    public function startAsyncUpdate(MapClient $mapClient): void
    {
        async(function () use ($mapClient) {
            $frameDurationInSeconds = 1 / max(1, $this->fps);
            do {
                $this->renderScreen(
                    $mapClient->getEntityCollection(),
                    $mapClient->getMapDimensions(),
                    $mapClient->getBackgroundColorData(),
                    $mapClient->getViewer(),
                    $mapClient->getClientIdString(),
                    $mapClient->getScreenId(),
                );
                delay($frameDurationInSeconds);
            } while (1);
        });
    }

    /**
     * @param ConsoleColor[][] $backgroundColorData
     */
    private function renderScreen(
        ?EntityCollection $entityCollection,
        ?Dimension2D $mapDimensions,
        array $backgroundColorData,
        ?Entity $viewer,
        string $clientIdString,
        int $screenId,
    ): void {
        if (!$entityCollection || !$mapDimensions || !$viewer) {
            system('clear');
            echo "Loading...\n\n";
            echo sprintf(
                "%s\nScreenId %d\n",
                $clientIdString,
                $screenId
            );
            return;
        }

        $entityMap = $this->prepareEntityMap($entityCollection);
        $backgroundColorMap = $backgroundColorData;

        $this->screen->setScreenData(
            $entityMap,
            $backgroundColorMap,
            $entityCollection,
            $mapDimensions,
            $viewer,
            MapSymbol::class
        );

        $this->screen->swapFrame();
    }

    /**
     * @return EntityCollection[][]
     */
    private function prepareEntityMap(EntityCollection $entityCollection): array
    {
        /** @var EntityCollection[][] $entityMap */
        $entityMap = [];

        /** @var Entity $entity */
        foreach ($entityCollection as $entity) {
            /** @var MapPosition $position */
            $position = $entity->getComponent(MapPosition::class);
            if ($position) {
                $entityMap[$position->getX()][$position->getY()] =
                    $entityMap[$position->getX()][$position->getY()] ?? new EntityCollection();

                $entityMap[$position->getX()][$position->getY()]->addEntity($entity);
            }
        }

        return $entityMap;
    }
}
