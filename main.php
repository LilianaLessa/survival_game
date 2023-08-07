<?php

declare(strict_types=1);

use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Component\Player;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\Engine\System\MapDrawUpdater;
use App\Engine\System\Physics;
use App\Engine\System\PlayerController;
use App\Engine\System\ReceiverSystemInterface;
use App\System\CommandPredicate;
use App\System\World;

require 'vendor/autoload.php';

$entityManager = new EntityManager();

$playerEntity = new Entity(
    1,
    new Player(),
    new MapPosition(5,5),
    new MapSymbol('P'),
);

$entityManager->addEntity($playerEntity);

$world = new World(10, 10);

$systems = [
    new MapDrawUpdater($world),
    new Physics($world),
    new PlayerController(),
];


do {
    //steps, in order:
    //process ai (process and move autonomous entities)
    //process physics (check if the entities can move. if so, move them)
    //update map draw
    foreach ($systems as $system) {
        if ($system instanceof Physics) {
            $system->process($entityManager->getEntities());
        }
    }

    // process world (plant growth, ore regen, entity spawn, etc)

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
    $command = strtolower(readline(">> "));

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
