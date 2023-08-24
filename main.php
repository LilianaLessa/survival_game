<?php

declare(strict_types=1);

use App\Engine\System\AISystemInterface;
use App\Engine\System\BattleSystem;
use App\Engine\System\ColorEffectsSystem;
use App\Engine\System\ItemCollection\CollectItems;
use App\Engine\System\ItemCollection\EntityBehaviorSystem;
use App\Engine\System\MonsterSpawner;
use App\Engine\System\MovementApplier;
use App\Engine\System\PhysicsSystemInterface;
use App\Engine\System\PlayerController;
use App\Engine\System\PlayerSpawner;
use App\Engine\System\WorldActionApplier;
use App\Engine\System\WorldController;
use App\Engine\System\WorldSystemInterface;
use App\System\AI\BehaviorPresetLibrary;
use App\System\Biome\BiomeGeneratorService;
use App\System\Biome\BiomePresetLibrary;
use App\System\Item\ItemPresetLibrary;
use App\System\Kernel;
use App\System\Monster\MonsterPresetLibrary;
use App\System\Monster\Spawner\MonsterSpawnerLibrary;
use App\System\Player\PlayerPresetLibrary;
use App\System\Screen\ScreenUpdater;
use App\System\TCP\TCPServer;
use App\System\World\WorldManager;
use App\System\World\WorldPresetLibrary;
use function Amp\delay;
use function Amp\async;

require_once 'vendor/autoload.php';

echo "loading AI...\n"; Kernel::getContainer()->get(BehaviorPresetLibrary::class)->load('./data/Entity/AI/Behavior');
echo "loading monsters...\n"; Kernel::getContainer()->get(MonsterPresetLibrary::class)->load('./data/Entity/Monster');
echo "loading spawners...\n"; Kernel::getContainer()->get(MonsterSpawnerLibrary::class)->load('./data/Entity/Monster');
echo "loading player config...\n"; Kernel::getContainer()->get(PlayerPresetLibrary::class)->load('./data/Player');
echo "loading world config...\n"; Kernel::getContainer()->get(WorldPresetLibrary::class)->load('./data/World');
echo "loading biomes...\n"; Kernel::getContainer()->get(BiomePresetLibrary::class)->load('./data/World');
echo "loading items...\n"; Kernel::getContainer()->get(ItemPresetLibrary::class)->load('./data/Item');

$worldManager = Kernel::getContainer()->get(WorldManager::class)->setMapBiomeData(
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
        })();

        return $mapBiomeData;
    })()
);

$systems = [
    ///...Kernel::getRegisteredGameSystemInstances(),
    Kernel::getContainer()->get(WorldActionApplier::class),
    Kernel::getContainer()->get(CollectItems::class),
    Kernel::getContainer()->get(MovementApplier::class),
    Kernel::getContainer()->get(BattleSystem::class),
    Kernel::getContainer()->get(ColorEffectsSystem::class),
    Kernel::getContainer()->get(PlayerSpawner::class),
    Kernel::getContainer()->get(EntityBehaviorSystem::class),
    Kernel::getContainer()->get(WorldController::class),
    Kernel::getContainer()->get(MonsterSpawner::class),

    //todo this should be attached to the player cli/unblocking cli socket.
    Kernel::getContainer()->get(PlayerController::class),
];

(new TCPServer('127.0.0.1:1988', $systems))->init();
Kernel::getContainer()->get(ScreenUpdater::class)->startAsyncUpdate();

function gameTick(): void
{
    $tickDurationInSeconds = 0.1;

    delay($tickDurationInSeconds); //tick
}

do { //game loop
    //steps, in order:
    // process world (plant growth, ore regen, entity spawn, etc)
    foreach ($systems as $system) {
        if ($system instanceof WorldSystemInterface) {
            $system->process();
        }
    }

    //process ai (process and move autonomous entities)
    foreach ($systems as $system) {
        if ($system instanceof AISystemInterface) {
            $system->process();
        }
    }

    //process physics (movement applier, fluid flow, and so on)
    foreach ($systems as $system) {
        if ($system instanceof PhysicsSystemInterface) {
            $system->process();
        }
    }

    gameTick();
} while(1);
