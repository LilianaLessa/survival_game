<?php

declare(strict_types=1);

use App\Engine\Component\Player;
use App\Engine\Entity\EntityManager;
use App\Engine\System\AISystemInterface;
use App\Engine\System\FluidDynamics;
use App\Engine\System\MapDrawUpdater;
use App\Engine\System\MonsterController;
use App\Engine\System\MonsterSpawner;
use App\Engine\System\MovementApplier;
use App\Engine\System\PhysicsSystemInterface;
use App\Engine\System\PlayerController;
use App\Engine\System\TreeSpawner;
use App\Engine\System\WorldController;
use App\Engine\System\WorldSystemInterface;
use App\System\Screen\ScreenUpdater;
use App\System\TCP\TCPServer;
use App\System\World;
use function Amp\delay;
use function Amp\async;

require_once 'vendor/autoload.php';

$entityManager = new EntityManager();

Player::createPlayer($entityManager, 5,5);

$world = new World(10, 10);
$screenUpdater = new ScreenUpdater($world, 10);

$systems = [
    new MapDrawUpdater($world, $entityManager),
    new MovementApplier($world, $entityManager),
    new FluidDynamics($world, $entityManager),
    //new FireDynamics($world, $entityManager),
    //new SoundDynamics($world, $entityManager),
    new MonsterSpawner($world, $entityManager),
    new TreeSpawner($world, $entityManager),

    //controllers
    new MonsterController($entityManager),
    new PlayerController($world, $entityManager),
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
readline('Press enter to start the game loop.');
$commandReceiver->init();

$screenUpdater->intiScreenUpdate();


$tickDurationInSeconds = 0.1;
do { //game loop

    //steps, in order:

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

    // process world (plant growth, ore regen, entity spawn, etc)
    foreach ($systems as $system) {
        if ($system instanceof WorldSystemInterface) {
            $system->process();
        }
    }

    //update map draw
    foreach ($systems as $system) {
        if ($system instanceof MapDrawUpdater) {
            $system->process();
        }
    }
    
    delay($tickDurationInSeconds); //tick
} while(1);
