<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Commands\MoveEntity;
use App\Engine\Component\MapPosition;
use App\Engine\Component\Movable;
use App\Engine\Component\Player;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\CommandParserTrait;
use App\System\CommandPredicate;
use App\System\Direction;

class PlayerController implements ReceiverSystemInterface
{
    use CommandParserTrait;

    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function parse(string $rawCommand): void
    {
        [$commandPredicate] = $this->extractCommand($rawCommand);

        $entityCollection = $this->entityManager->getEntitiesWithComponents(
            Movable::class,
            Player::class,
            MapPosition::class
        );

        /** @var Movable $movementQueue */
        foreach ($entityCollection as [$movementQueue]) {
            $this->parseMovementCommand($commandPredicate, $movementQueue);

            break;
        }
    }

    private function parseMovementCommand(mixed $commandPredicate, Movable $movementQueue): void
    {
        $moveCommand = match ($commandPredicate) {
            CommandPredicate::PLAYER_MOVE_UP => new MoveEntity(Direction::UP),
            CommandPredicate::PLAYER_MOVE_DOWN => new MoveEntity(Direction::DOWN),
            CommandPredicate::PLAYER_MOVE_LEFT => new MoveEntity(Direction::LEFT),
            CommandPredicate::PLAYER_MOVE_RIGHT => new MoveEntity(Direction::RIGHT),
            default => null,
        };

        $moveCommand && $movementQueue->add($moveCommand);
    }
}
