<?php

declare(strict_types=1);

use App\Engine\Component\Player;
use App\Engine\Entity\EntityManager;
use App\Engine\System\AISystemInterface;
use App\Engine\System\MapDrawUpdater;
use App\Engine\System\MonsterController;
use App\Engine\System\MonsterSpawner;
use App\Engine\System\Physics;
use App\Engine\System\PlayerController;
use App\Engine\System\ReceiverSystemInterface;
use App\Engine\System\TreeSpawner;
use App\Engine\System\WorldSystemInterface;
use App\System\CommandPredicate;
use App\System\World;

require 'vendor/autoload.php';

$entityManager = new EntityManager();

$entityManager->addEntity(
    Player::createPlayer(1, 5,5)
);

$world = new World(10, 10);

$systems = [
    new MapDrawUpdater($world),
    new Physics($world),
    new PlayerController(),
    new MonsterSpawner($world, $entityManager),
    new TreeSpawner($world, $entityManager),
    new MonsterController(),
];

do {
    //steps, in order:

    //process ai (process and move autonomous entities)
    foreach ($systems as $system) {
        if ($system instanceof AISystemInterface) {
            $system->process($entityManager->getEntities());
        }
    }

    //process physics (check if the entities can move. if so, move them)
    foreach ($systems as $system) {
        if ($system instanceof Physics) {
            $system->process($entityManager->getEntities());
        }
    }

    // process world (plant growth, ore regen, entity spawn, etc)
    foreach ($systems as $system) {
        if ($system instanceof WorldSystemInterface) {
            $system->process($entityManager->getEntities());
        }
    }

    //update map draw
    foreach ($systems as $system) {
        if ($system instanceof MapDrawUpdater) {
            $system->process($entityManager->getEntities());
        }
    }

    //draw map
    $world->draw();

    //draw system messages

    //draw command input
    echo "\n\n";
    $command = '';//strtolower(readline(">> "));
    usleep(500000);

    //receive commands (player awsd, mine, and so on)
    if ($command) {
        foreach ($systems as $system) {
            if ($system instanceof ReceiverSystemInterface) {
                $system->parse($command, $entityManager->getEntities());
            }
        }
    }
} while(CommandPredicate::tryFrom($command) !== CommandPredicate::EXIT);

echo "good bye";
