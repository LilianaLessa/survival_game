<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Commands\InspectCell;
use App\Engine\Commands\InvokableCommandInterface;
use App\Engine\Commands\MoveEntity;
use App\Engine\Commands\SetMapViewport;
use App\Engine\Commands\ShowInventory;
use App\Engine\Commands\WhereAmI;
use App\Engine\Commands\WorldAction;
use App\Engine\Component\Item\Inventory;
use App\Engine\Component\MapPosition;
use App\Engine\Component\Movable;
use App\Engine\Component\Player;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\CommandParserTrait;
use App\System\CommandPredicate;
use App\System\Direction;
use App\System\World;

class PlayerController implements ReceiverSystemInterface
{
    use CommandParserTrait;

    public function __construct(private readonly World $world, private readonly EntityManager $entityManager)
    {
    }

    public function parse(string $rawCommand): void
    {
        [$commandPredicate, $commandArguments] = $this->extractCommand($rawCommand);

        $entityCollection = $this->entityManager->getEntitiesWithComponents(
            Player::class,
            Movable::class,
            MapPosition::class,
            Inventory::class,
        );

        /** @var Movable $movable */
        /** @var MapPosition $position */
        /** @var Inventory $inventory */
        foreach ($entityCollection as $entityId => [,$movable, $position, $inventory]) {
            $this->parseMovementCommand($commandPredicate, $movable);
            $this->parseDebugCommand($commandPredicate, $commandArguments, $position);
            $this->parseInfoCommand($commandPredicate, $commandArguments, $position, $inventory);
            $this->parseActionOnWorldCommand($commandPredicate, $commandArguments, $entityId);

            break;
        }
    }

    private function parseMovementCommand(?CommandPredicate $commandPredicate, Movable $movementQueue): void
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

    private function parseInfoCommand(
        ?CommandPredicate $commandPredicate,
        array $commandArguments,
        MapPosition $position,
        Inventory $inventory
    ): void {
        $command = match ($commandPredicate) {
            CommandPredicate::PLAYER_SELF_WHERE => new WhereAmI($position),
            CommandPredicate::PLAYER_VIEWPORT => new SetMapViewport($this->world, $commandArguments),
            CommandPredicate::INVENTORY => new ShowInventory($inventory),
            default => null,
        };

        $command && $command();
    }

    private function parseActionOnWorldCommand(
        ?CommandPredicate $commandPredicate,
        array $commandArguments,
        string $entityId,
    ): void {
        $command = match ($commandPredicate) {
            CommandPredicate::PLAYER_ACTION => new WorldAction(
                $this->entityManager,
                $entityId,
                $commandArguments[0],
                Direction::tryFrom($commandArguments[1] ?? null)
            ),
            default => null,
        };

        $command && $command();
    }

    private function parseDebugCommand(
        ?CommandPredicate $commandPredicate,
        array $commandArguments,
        MapPosition $position,
    ): void {
        $skillCommand = match ($commandPredicate) {
            CommandPredicate::DEBUG_INSPECT_CELL => new InspectCell(
                world: $this->world,
                from: $position,
                direction: Direction::tryFrom($commandArguments[0] ?? ''),
            ),
            default => null,
        };

        $skillCommand instanceof InvokableCommandInterface && $skillCommand();
    }
}
