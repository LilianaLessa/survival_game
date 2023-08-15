<?php

declare(strict_types=1);

use App\Engine\Component\Player;
use App\Engine\Entity\EntityManager;
use App\Engine\System\AISystemInterface;
use App\Engine\System\Battler;
use App\Engine\System\FluidDynamics;
use App\Engine\System\ItemCollection\CollectItems;
use App\Engine\System\ItemCollection\EntityBehaviorSystem;
use App\Engine\System\MonsterSpawner;
use App\Engine\System\MovementApplier;
use App\Engine\System\PhysicsSystemInterface;
use App\Engine\System\PlayerController;
use App\Engine\System\TreeSpawner;
use App\Engine\System\WorldActionApplier;
use App\Engine\System\WorldController;
use App\Engine\System\WorldSystemInterface;
use App\System\AI\AiBehaviorManager;
use App\System\Item\ItemManager;
use App\System\Monster\MonsterPresetLibrary;
use App\System\Screen\ScreenUpdater;
use App\System\TCP\TCPServer;
use App\System\World;
use function Amp\delay;

require_once 'vendor/autoload.php';

$entityManager = new EntityManager();

$aiBehaviorManager = new AiBehaviorManager(
    './data/Entity/AI/Behavior',
);

$aiBehaviorManager->load();

$monsterPresetLibrary = new MonsterPresetLibrary(
    $aiBehaviorManager
);
$monsterPresetLibrary->load('./data/Entity/Monster');

//die();




$itemManager = new ItemManager();
$itemManager->loadItems('./data/Item/items.json');

$worldWidth = 10;
$worldHeight = 10;
$initialViewportWidth = 50;
$initialViewportHeight = 50;

Player::createPlayer($entityManager, rand(0,$worldWidth-1),rand(0,$worldHeight-1));

$world = new World($entityManager,$worldWidth, $worldHeight, $initialViewportWidth, $initialViewportHeight);

$systems = [
    new WorldActionApplier($world, $entityManager),
    new CollectItems($world, $entityManager),
    new MovementApplier($world, $entityManager),
    new Battler($entityManager),
    new FluidDynamics($world, $entityManager),
    //new FireDynamics($world, $entityManager),
    //new SoundDynamics($world, $entityManager),
    new EntityBehaviorSystem($entityManager),
    new MonsterSpawner(
        $world,
        $itemManager,
        $entityManager,
        $monsterPresetLibrary,
        (int) ceil(($worldWidth * $worldHeight) * 0.005)
    ),
    //new TreeSpawner($world, $entityManager, $itemManager, (int) ceil(($worldWidth * $worldHeight) * 0.1)),
    //controllers
    //new MonsterController($entityManager),
    //todo this should be attached to the player cli/unblocking cli socket.
    new PlayerController($world, $entityManager, $itemManager),
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

$screenUpdater = new ScreenUpdater($entityManager, $world, 20);
$screenUpdater->intiScreenUpdate();

$tickDurationInSeconds = 0.1;
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

    delay($tickDurationInSeconds); //tick
} while(1);
