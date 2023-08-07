<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Commands\MoveEntity;
use App\Engine\Component\MapPosition;
use App\Engine\Component\Player;
use App\Engine\Entity\Entity;
use App\System\CommandPredicate;
use App\System\Direction;

class PlayerController implements ReceiverSystemInterface
{

    /** @param Entity[] $entityCollection */
    public function parse(string $command, array $entityCollection): void
    {
        $commandArray = explode(' ',$command);
        $commandPredicate = array_shift($commandArray);

        foreach ($entityCollection as $entity) {
            $player = $entity->getComponent(Player::class);
            $position = $entity->getComponent(MapPosition::class);

            if ($player && $position) {
                $command = match (CommandPredicate::tryFrom($commandPredicate)) {
                    CommandPredicate::UP => new MoveEntity(Direction::UP),
                    CommandPredicate::DOWN => new MoveEntity(Direction::DOWN),
                    CommandPredicate::LEFT => new MoveEntity(Direction::LEFT),
                    CommandPredicate::RIGHT => new MoveEntity(Direction::RIGHT),
                    default => null,
                };

                $command && $entity->addCommand($command);

                break;
            }
        }
    }
}
