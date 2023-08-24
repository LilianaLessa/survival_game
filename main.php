<?php

declare(strict_types=1);

use App\Engine\Entity\EntityManager;
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
use App\Engine\System\TreeSpawner;
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
use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;
use function Amp\delay;

require_once 'vendor/autoload.php';

function colorCheck(): void
{
    $consoleColor = new ConsoleColor();

    echo "Colors are supported: " . ($consoleColor->isSupported() ? 'Yes' : 'No') . "\n";
    echo "256 colors are supported: " . ($consoleColor->are256ColorsSupported() ? 'Yes' : 'No') . "\n\n";

    if ($consoleColor->isSupported()) {
        foreach ($consoleColor->getPossibleStyles() as $style) {
            echo $consoleColor->apply($style, $style) . "\n";
        }
    }

    echo "\n";

    if ($consoleColor->are256ColorsSupported()) {
        echo "Foreground colors:\n";
        for ($i = 1; $i <= 255; $i++) {
            echo $consoleColor->apply("color_$i", str_pad((string)$i, 6, ' ', STR_PAD_BOTH));

            if ($i % 15 === 0) {
                echo "\n";
            }
        }

        echo "\nBackground colors:\n";

        for ($i = 1; $i <= 255; $i++) {
            echo $consoleColor->apply("bg_color_$i", str_pad((string)$i, 6, ' ', STR_PAD_BOTH));

            if ($i % 15 === 0) {
                echo "\n";
            }
        }

        echo "\n";
    }
}

//colorCheck();
//readline('Press enter to continue.');

/** @var PlayerPresetLibrary $playerPresetLibrary */
$playerPresetLibrary = Kernel::getContainer()->get(PlayerPresetLibrary::class);
$behaviorPresetLibrary = Kernel::getContainer()->get(BehaviorPresetLibrary::class);
$monsterPresetLibrary = Kernel::getContainer()->get(MonsterPresetLibrary::class);
$monsterSpawnerLibrary = Kernel::getContainer()->get(MonsterSpawnerLibrary::class);
/** @var WorldPresetLibrary $worldPresetLibrary */
$worldPresetLibrary = Kernel::getContainer()->get(WorldPresetLibrary::class);
$itemPresetLibrary =  Kernel::getContainer()->get(ItemPresetLibrary::class);
$biomePresetLibrary =  Kernel::getContainer()->get(BiomePresetLibrary::class);

$behaviorPresetLibrary->load('./data/Entity/AI/Behavior');
$monsterPresetLibrary->load('./data/Entity/Monster');
$monsterSpawnerLibrary->load('./data/Entity/Monster');
$worldPresetLibrary->load('./data/World');
$biomePresetLibrary->load('./data/World');
$playerPresetLibrary->load('./data/Player');
$itemPresetLibrary->load('./data/Item');

/** @var EntityManager $entityManager */
$entityManager = Kernel::getContainer()->get(EntityManager::class);

$worldPreset = $worldPresetLibrary->getDefaultWorldPreset();
$worldWidth = $worldPreset->getMapWidth();
$worldHeight = $worldPreset->getMapHeight();

$playerPreset = $playerPresetLibrary->getDefaultPlayerPreset();
$initialViewportWidth = $playerPreset->getInitialViewportWidth();
$initialViewportHeight = $playerPreset->getInitialViewportHeight();

/** @var BiomeGeneratorService $biomeGenerator */
$biomeGenerator = Kernel::getContainer()->get(BiomeGeneratorService::class);
$mapBiomeData = $biomeGenerator->generate();

/** @var WorldManager $worldManager */
$worldManager = Kernel::getContainer()->get(WorldManager::class);
$worldManager->setMapBiomeData($mapBiomeData);

$screenUpdater = new ScreenUpdater($entityManager, $worldManager, $worldPreset->getScreenUpdaterFps());

$systems = [
    ///...Kernel::getRegisteredGameSystemInstances(),
    Kernel::getContainer()->get(WorldActionApplier::class),
    Kernel::getContainer()->get(CollectItems::class),
    Kernel::getContainer()->get(MovementApplier::class),
    Kernel::getContainer()->get(BattleSystem::class),
    Kernel::getContainer()->get(ColorEffectsSystem::class),
    Kernel::getContainer()->get(PlayerSpawner::class),
    Kernel::getContainer()->get(EntityBehaviorSystem::class),
    Kernel::getContainer()->get(PlayerController::class),
    Kernel::getContainer()->get(WorldController::class),
    Kernel::getContainer()->get(MonsterSpawner::class),

    //new TreeSpawner($worldManager, $entityManager, $itemPresetLibrary, (int) ceil(($worldWidth * $worldHeight) * 0.1)),
    //controllers
    //new MonsterController($entityManager),
    //todo this should be attached to the player cli/unblocking cli socket.

];

$commandReceiver = new TCPServer('127.0.0.1:1988', $systems);

/**
 * todo
 *  separate the map render from the game logics from the player cli.
 *  the map renderer will receive information from the game logic.
 *  the player cli will send and receive information to the game logic.
 *  multiple instances of the map renderer can be created, and connected to a single player input.
 *
 */

$commandReceiver->init();
$screenUpdater->intiScreenUpdate();

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
