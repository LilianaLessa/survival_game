<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Commands\GiveItemToPlayer;
use App\Engine\Commands\InspectCell;
use App\Engine\Commands\InspectEntity;
use App\Engine\Commands\MoveEntity;
use App\Engine\Commands\SetMapViewport;
use App\Engine\Commands\SetPlayerName;
use App\Engine\Commands\ShowInventory;
use App\Engine\Commands\WhereAmI;
use App\Engine\Commands\WorldAction;
use App\Engine\Component\Fluid;
use App\Engine\Component\Item\Inventory;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Component\MovementQueue;
use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\EntityManager;
use App\Engine\Trait\CommandParserTrait;
use App\System\CommandPredicate;
use App\System\Direction;
use App\System\Event\Dispatcher;
use App\System\Event\Event\DebugMessageEvent;
use App\System\Event\Event\UiMessageEvent;
use App\System\Helpers\Point2D;
use App\System\Item\ItemPresetLibrary;
use App\System\World\WorldManager;

class PlayerController implements WorldSystemInterface
{
    use CommandParserTrait;

    public function __construct(
        private readonly WorldManager $world,
        private readonly EntityManager $entityManager,
        private readonly ItemPresetLibrary $itemManager,
    )
    {
    }

    //todo movement actions would also change the player facing, so no need of sending a main action based on
    //     directions.
    //     so, the arrows would be used to control the aim for long-distance skills.
    //     or when selected the skill, the aim should appear and the action arrows should be disabled.
    //     maybe this is better, as there's not a way to show character sight direction yet,
    public function process(): void
    {
        $entityCollection = $this->entityManager->getEntitiesWithComponents(
            PlayerCommandQueue::class,
            MovementQueue::class,
            MapPosition::class,
            Inventory::class,
        );

        /** @var PlayerCommandQueue $playerCommandQueue */
        /** @var MovementQueue $movable */
        /** @var MapPosition $position */
        /** @var Inventory $inventory */
        foreach ($entityCollection as $entityId => [$playerCommandQueue ,$movable, $position, $inventory]) {
            $commandQueue = $playerCommandQueue->getCommandQueue();
            while (!$commandQueue->isEmpty() &&$rawCommand = $commandQueue->dequeue()) {
                try {
                    [$commandPredicate, $commandArguments] = $this->extractCommand($rawCommand);

                    $this->parseMovementCommand($commandPredicate, $commandArguments, $movable, $position);
                    $this->parseDebugCommand(
                        $commandPredicate,
                        $commandArguments,
                        $position,
                        $playerCommandQueue,
                        $inventory
                    );
                    $this->parseInfoCommand(
                        $commandPredicate,
                        $commandArguments,
                        $position,
                        $inventory,
                        $playerCommandQueue,
                        $entityId
                    );
                    $this->parseActionOnWorldCommand(
                        $commandPredicate,
                        $commandArguments,
                        $entityId,
                        $playerCommandQueue
                    );
                    $this->parseWorldViewChange(
                        $commandPredicate,
                        $commandArguments,
                        $playerCommandQueue
                    );
                } catch (\Throwable $e) {
                    $uiMessage =  sprintf(
                        "\nException on parsing player command (%s): %s\n",
                        $rawCommand,
                        $e->getMessage(),
                    );

                    Dispatcher::getInstance()->dispatch(new DebugMessageEvent($uiMessage, $playerCommandQueue));
                }
            }
        }
    }

    private function parseMovementCommand(
        ?CommandPredicate $commandPredicate,
        array $commandArguments,
        MovementQueue $movementQueue,
        MapPosition $from
    ): void {
        $moveCommand = match ($commandPredicate) {
            CommandPredicate::PLAYER_MOVE_UP =>
                new MoveEntity($this->calculateTargetCoordinates($from, Direction::UP)),
            CommandPredicate::PLAYER_MOVE_DOWN =>
                new MoveEntity($this->calculateTargetCoordinates($from, Direction::DOWN)),
            CommandPredicate::PLAYER_MOVE_LEFT =>
                new MoveEntity($this->calculateTargetCoordinates($from, Direction::LEFT)),
            CommandPredicate::PLAYER_MOVE_RIGHT =>
                new MoveEntity($this->calculateTargetCoordinates($from, Direction::RIGHT)),
            CommandPredicate::PLAYER_WARP =>
                new MoveEntity(new Point2D((int)$commandArguments[0], (int)$commandArguments[1])),
            default => null,
        };

        $moveCommand && $movementQueue->add($moveCommand);
    }

    private function parseInfoCommand(
        ?CommandPredicate $commandPredicate,
        array $commandArguments,
        MapPosition $position,
        Inventory $inventory,
        PlayerCommandQueue $playerCommandQueue,
        string $entityId,
    ): void {
        $command = match ($commandPredicate) {
            CommandPredicate::PLAYER_SELF_WHERE => new WhereAmI($position),
            CommandPredicate::PLAYER_VIEWPORT => new SetMapViewport(
                $this->world,
                $this->entityManager,
                $entityId,
                $commandArguments
            ),
            CommandPredicate::INVENTORY => new ShowInventory($inventory, (bool)($commandArguments[0] ?? false)),
            default => null,
        };

        $command && $command($playerCommandQueue);
    }

    private function parseActionOnWorldCommand(
        ?CommandPredicate $commandPredicate,
        array $commandArguments,
        string $entityId,
        PlayerCommandQueue $playerCommandQueue,
    ): void {
        $command = match ($commandPredicate) {
            CommandPredicate::PLAYER_ACTION => new WorldAction(
                $this->entityManager,
                $entityId,
                $commandArguments[0],
                Direction::tryFrom($commandArguments[1] ?? null)
            ),
            CommandPredicate::PLAYER_SET_NAME => new SetPlayerName(
                $this->entityManager,
                $entityId,
                $commandArguments[0],
            ),
            default => null,
        };

        $command && $command($playerCommandQueue);
    }

    //todo these commands should be moved to their own debug controller.
    private function parseDebugCommand(
        ?CommandPredicate $commandPredicate,
        array $commandArguments,
        MapPosition $position,
        PlayerCommandQueue $playerCommandQueue,
        Inventory $inventory,
    ): void {
        $debugCommand = match ($commandPredicate) {
            CommandPredicate::DEBUG_INSPECT_CELL => new InspectCell(
                world: $this->world,
                from: $position,
                direction: Direction::tryFrom($commandArguments[0] ?? ''),
            ),
            CommandPredicate::DEBUG_INSPECT_ENTITY => new InspectEntity(
                $commandArguments[0] ?? '',
                $commandArguments[1] ?? '',
            ),
            CommandPredicate::DEBUG_GIVE_ITEM => new GiveItemToPlayer(
                $this->entityManager,
                $this->itemManager,
                $inventory,
                $commandArguments[0] ?? 'wood',
                (int) ($commandArguments[1] ?? 1),
            ),
            default => null,
        };

        $debugCommand && $debugCommand($playerCommandQueue);
    }

    private function calculateTargetCoordinates(MapPosition $from, Direction $direction): Point2D
    {
        $diff = match ($direction) {
            Direction::UP => [0,-1],
            Direction::DOWN => [0,1],
            Direction::LEFT => [-1,0],
            Direction::RIGHT => [1,0],
            default => [0,0],
        };

        return new Point2D(
            $from->getX() + $diff[0],
            $from->getY() + $diff[1],
        );
    }

    private function parseWorldViewChange(
        ?CommandPredicate $commandPredicate,
        array $commandArguments,
        PlayerCommandQueue $playerCommandQueue,
    ): void {
        $execute = match ($commandPredicate) {
            CommandPredicate::WORLD_SET_VIEW => function () use ($commandArguments, $playerCommandQueue) {
                $worldType = $commandArguments[0] ?? 'world';
                $worldViewType = match ($worldType) {
                    'fluid' => Fluid::class,
                    'world' => MapSymbol::class,
                    default => (function () use (&$worldType) { $worldType = 'world'; return null;})(),
                };

                $this->world->setDrawableClass($worldViewType);

                $uiMessage = sprintf("World view mode set to %s\n", $worldType);

                Dispatcher::getInstance()->dispatch(new UiMessageEvent($uiMessage, $playerCommandQueue));
            },
            default => null
        };

        $execute && $execute();
    }
}
