<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Commands\GiveItemToPlayer;
use App\Engine\Commands\InspectCell;
use App\Engine\Commands\InspectEntity;
use App\Engine\Commands\MoveEntity;
use App\Engine\Commands\SetMapViewport;
use App\Engine\Commands\ShowInventory;
use App\Engine\Commands\WhereAmI;
use App\Engine\Commands\WorldAction;
use App\Engine\Component\Item\Inventory;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MovementQueue;
use App\Engine\Component\Player;
use App\Engine\Entity\EntityManager;
use App\Engine\System\ReceiverSystemInterface;
use App\Engine\Trait\CommandParserTrait;
use App\System\CommandPredicate;
use App\System\Direction;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\Helpers\Point2D;
use App\System\Item\ItemPresetLibrary;
use App\System\World\WorldManager;
use SplQueue;

class PlayerCommandQueue implements ComponentInterface
{
    private readonly SplQueue $commandQueue;

    public function __construct(private readonly string $socketUuid)
    {
        $this->commandQueue = new SplQueue();
    }

    public function getCommandQueue(): SplQueue
    {
        return $this->commandQueue;
    }

    public function getSocketUuid(): string
    {
        return $this->socketUuid;
    }
}
