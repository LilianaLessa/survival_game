<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Commands\MoveEntity;
use App\Engine\Component\MapPosition;
use App\Engine\Component\Player;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\CommandPredicate;
use App\System\Direction;

class PlayerController implements ReceiverSystemInterface
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    /** @param Entity[] $entityCollection */
    public function parse(string $command): void
    {
        $commandArray = explode(' ', $command);
        $commandPredicate = array_shift($commandArray);

        $entityCollection = $this->entityManager->getEntitiesWithComponents(
            Player::class,
            MapPosition::class
        );

        /** @var Player $player */
        foreach ($entityCollection as $entityId => $components) {
            $command = match (CommandPredicate::tryFrom($commandPredicate)) {
                CommandPredicate::UP => new MoveEntity(Direction::UP),
                CommandPredicate::DOWN => new MoveEntity(Direction::DOWN),
                CommandPredicate::LEFT => new MoveEntity(Direction::LEFT),
                CommandPredicate::RIGHT => new MoveEntity(Direction::RIGHT),
                default => null,
            };

            $command && $this->entityManager->getEntityById($entityId)->addCommand($command);

            break;
        }
    }

}
