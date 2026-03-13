<?php

declare(strict_types=1);

namespace App;

use App\Engine\Component\DrawableInterface;
use App\Engine\Component\MapPosition;
use App\Engine\Entity\EntityCollection;
use App\Engine\Entity\EntityManager;
use App\Engine\System\AISystemInterface;
use App\Engine\System\GameSystemInterface;
use App\Engine\System\PhysicsSystemInterface;
use App\Engine\System\WorldSystemInterface;
use App\System\Biome\BiomeGeneratorService;
use App\System\Event\Event\AbstractEventListener;
use App\System\Kernel;
use App\System\PresetLibrary\PresetLibrariesLoader;
use App\System\Screen\ScreenUpdater;
use App\System\TCP\TCPServer;
use App\System\World\WorldManager;
use function Amp\async;
use function Amp\delay;

class Game
{
    /** @var GameSystemInterface[] */
    private array $gameSystems;
    private ?float $lastDraw = null;

    public function init(?float $worldSeed = null): void
    {
        $this->loadDataLibraries();
        $this->initWorld($worldSeed);
        $this->initEventListeners();
        $this->loadGameSystems();

        $this->startServer();
    }

    public function start(): void
    {
        $systemInterfaces = [
            WorldSystemInterface::class => Kernel::getAllRegisteredConcreteInstances(
                WorldSystemInterface::class,
            ), // process world (plant growth, ore regen, entity spawn, etc)
            AISystemInterface::class => Kernel::getAllRegisteredConcreteInstances(
                AISystemInterface::class,
            ), //process ai (process and move autonomous entities)
            PhysicsSystemInterface::class => Kernel::getAllRegisteredConcreteInstances(
                PhysicsSystemInterface::class,
            ), //process physics (movement applier, fluid flow, and so on)
        ];

        /** @var EntityManager $entityManager */
        $entityManager = Kernel::getContainer()->get(EntityManager::class);

        echo "Starting game loop.\n\n";

        while (1) { //game loop
            foreach ($systemInterfaces as $systems) {
                foreach ($systems as $system) {
                    $system->process();
                }
            }

            //update entity map here?
            $this->updateWorldEntityMap();

            $this->gameTick();

            if ($this->lastDraw !== null) {
                echo sprintf(
                    "Loop Time: %f - EntityCount: %d            \r",
                    microtime(true) - $this->lastDraw,
                    count($entityManager->getEntityCollection())
                );
            }

            $this->lastDraw = microtime(true);
        }
    }

    private function loadDataLibraries(): void
    {
        Kernel::getContainer()->get(PresetLibrariesLoader::class)->load('./data');
    }

    private function initWorld(?float $seed = null): void
    {
        echo "\n";
        Kernel::getContainer()->get(WorldManager::class)->setTerrainData(
            (function () use ($seed): array {
                /** @var BiomeGeneratorService $biomeGenerator */
                $biomeGenerator = Kernel::getContainer()->get(BiomeGeneratorService::class);

                $mapBiomeData = null;
                async(function () use (&$mapBiomeData, $biomeGenerator, $seed) {
                    $mapBiomeData = $biomeGenerator->generate($seed);
                });

                (function () use (&$mapBiomeData) {
                    while ($mapBiomeData === null) {
                        echo sprintf("Generating world...\r");
                        delay(0.1);
                    }

                    echo "\n\n";
                })();

                return $mapBiomeData;
            })()
        );
    }

    private function initEventListeners(): void
    {
        Kernel::getAllRegisteredConcreteInstances(AbstractEventListener::class);
    }

    private function gameTick(): void
    {
        $tickDurationInSeconds = 0.01; //todo this is limiting the frames per second at the end

        delay($tickDurationInSeconds); //tick
    }

    private function loadGameSystems(): void
    {
        $this->gameSystems = Kernel::getRegisteredGameSystemInstances();
    }

    private function startServer(): void
    {
        //starting server
        (new TCPServer('127.0.0.1:1988'))->init();
    }

    public function initServerScreenUpdate(): void
    {
        /** @var ScreenUpdater $screenUpdater */
        $screenUpdater = Kernel::getContainer()->get(ScreenUpdater::class);
        $screenUpdater->startAsyncUpdate();
    }


    private function updateWorldEntityMap(): void
    {
        $entityManager = Kernel::getContainer()->get(EntityManager::class);
        $worldManager =  Kernel::getContainer()->get(WorldManager::class);


        /** @var EntityCollection[][] $entityMap */
        $entityMap = [];
        $entitiesToUpdate = $entityManager->getEntitiesWithComponents(
            MapPosition::class,
        );

        /** @var MapPosition $position */
        foreach ($entitiesToUpdate as $entityId => [$position]) {
            $entityMap[$position->getX()][$position->getY()] =
                $entityMap[$position->getX()][$position->getY()] ?? new EntityCollection();

            $entityMap[$position->getX()][$position->getY()]->addEntity(
                $entityManager->getEntityById($entityId)
            );
        }

        $worldManager->resetEntityMap();
        $worldManager->setEntityMap($entityMap);
    }

}
