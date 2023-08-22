<?php

declare(strict_types=1);

use App\Engine\Component\Player;
use App\Engine\Entity\EntityManager;
use App\Engine\System\AISystemInterface;
use App\Engine\System\BattleSystem;
use App\Engine\System\FluidDynamics;
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
use App\System\Item\ItemPresetLibrary;
use App\System\Kernel;
use App\System\Monster\MonsterPresetLibrary;
use App\System\Player\PlayerPresetLibrary;
use App\System\Screen\ScreenUpdater;
use App\System\TCP\TCPServer;
use App\System\World\WorldManager;
use App\System\World\WorldPresetLibrary;
use function Amp\delay;

require_once 'vendor/autoload.php';

/** @var PlayerPresetLibrary $playerPresetLibrary */
$playerPresetLibrary = Kernel::getContainer()->get(PlayerPresetLibrary::class);
$behaviorPresetLibrary = Kernel::getContainer()->get(BehaviorPresetLibrary::class);
$monsterPresetLibrary = Kernel::getContainer()->get(MonsterPresetLibrary::class);
/** @var WorldPresetLibrary $worldPresetLibrary */
$worldPresetLibrary = Kernel::getContainer()->get(WorldPresetLibrary::class);
$itemPresetLibrary =  Kernel::getContainer()->get(ItemPresetLibrary::class);

$behaviorPresetLibrary->load('./data/Entity/AI/Behavior');
$monsterPresetLibrary->load('./data/Entity/Monster');
$worldPresetLibrary->load('./data/World');
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

/** @var WorldManager $world */
$world = Kernel::getContainer()->get(WorldManager::class);

$systems = [
    new WorldActionApplier($world, $entityManager),
    new CollectItems($world, $entityManager),
    new MovementApplier($world, $entityManager),
    Kernel::getContainer()->get(BattleSystem::class),
    new FluidDynamics($world, $entityManager),
    //new FireDynamics($world, $entityManager),
    //new SoundDynamics($world, $entityManager),
    new EntityBehaviorSystem($entityManager),
    new MonsterSpawner(
        $world,
        $itemPresetLibrary,
        $entityManager,
        $monsterPresetLibrary,
        (int) ceil(($worldWidth * $worldHeight) * 0.005),
        'youngEquine'
    ),
    new MonsterSpawner(
        $world,
        $itemPresetLibrary,
        $entityManager,
        $monsterPresetLibrary,
        (int) ceil(($worldWidth * $worldHeight) * 0.005),
        'giantSnail'
    ),
    Kernel::getContainer()->get(PlayerSpawner::class),
    new TreeSpawner($world, $entityManager, $itemPresetLibrary, (int) ceil(($worldWidth * $worldHeight) * 0.1)),
    //controllers
    //new MonsterController($entityManager),
    //todo this should be attached to the player cli/unblocking cli socket.
    new PlayerController($world, $entityManager, $itemPresetLibrary),
    new WorldController($world),
];

/**
 * todo
 *  separate the map render from the game logics from the player cli.
 *  the map renderer will receive information from the game logic.
 *  the player cli will send and receive information to the game logic.
 *  multiple instances of the map renderer can be created, and connected to a single player input.
 *
 */
$commandReceiver = new TCPServer('127.0.0.1:1988', $systems);
//readline('Press enter to start the game server.');
$commandReceiver->init();

$screenUpdater = new ScreenUpdater($entityManager, $world, $worldPreset->getScreenUpdaterFps());
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
