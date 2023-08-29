<?php

declare(strict_types=1);

namespace App;

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

    public function init(): void
    {
        $this->loadDataLibraries();
        $this->initWorld();
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

    private function initWorld(): void
    {
        echo "\n";
        Kernel::getContainer()->get(WorldManager::class)->setTerrainData(
            (function (): array {
                /** @var BiomeGeneratorService $biomeGenerator */
                $biomeGenerator = Kernel::getContainer()->get(BiomeGeneratorService::class);

                $mapBiomeData = null;
                async(function () use (&$mapBiomeData, $biomeGenerator) {
                    $mapBiomeData = $biomeGenerator->generate();
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

    private function initEventListeners(): void {
        Kernel::getAllRegisteredConcreteInstances(AbstractEventListener::class);
    }

    private function gameTick(): void
    {
        $tickDurationInSeconds = 0.1;

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
}
