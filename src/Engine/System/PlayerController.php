<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Commands\GiveItemToPlayer;
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
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\Item\ItemManager;
use App\System\World;

class PlayerController implements ReceiverSystemInterface
{
    use CommandParserTrait;

    public function __construct(
        private readonly World $world,
        private readonly EntityManager $entityManager,
        private readonly ItemManager $itemManager,
    )
    {
    }

    //todo movement actions would also change the player facing, so no need of sending a main action based on
    //     directions.
    //     so, the arrows would be used to control the aim for long-distance skills.
    //     or when selected the skill, the aim should appear and the action arrows should be disabled.
    //     maybe this is better, as there's not a way to show character sight direction yet,
    public function parse(string $rawCommand): void
    {
        [$commandPredicate, $commandArguments] = $this->extractCommand($rawCommand);

        $entityCollection = $this->entityManager->getEntitiesWithComponents(
            Player::class,
            Movable::class,
            MapPosition::class,
            Inventory::class,
        );

        try {
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
        } catch (\Throwable $e) {
            Dispatcher::dispatch(
                new UiMessageEvent(
                    sprintf(
                        "\nException on parsing player command (%s): %s\n",
                        $rawCommand,
                        $e->getMessage(),
                    )
                )
            );
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
            CommandPredicate::INVENTORY => new ShowInventory($inventory, (bool)($commandArguments[0] ?? false)),
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

    //todo these commands should be moved to their own debug controller.
    private function parseDebugCommand(
        ?CommandPredicate $commandPredicate,
        array $commandArguments,
        MapPosition $position,
    ): void {
        $debugCommand = match ($commandPredicate) {
            CommandPredicate::DEBUG_INSPECT_CELL => new InspectCell(
                world: $this->world,
                from: $position,
                direction: Direction::tryFrom($commandArguments[0] ?? ''),
            ),
            CommandPredicate::DEBUG_GIVE_ITEM => new GiveItemToPlayer(
                $this->entityManager,
                $this->itemManager,
                $commandArguments[0] ?? 'wood',
                (int) ($commandArguments[1] ?? 1),
            ),
            default => null,
        };

        $debugCommand && $debugCommand();
    }
}
