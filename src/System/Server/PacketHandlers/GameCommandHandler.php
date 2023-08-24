<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\Socket\ResourceSocket;
use App\Engine\Component\PlayerCommandQueue;
use App\Engine\System\ReceiverSystemInterface;
use App\System\Kernel;
use App\System\Server\Client\ClientPool;
use Ramsey\Uuid\UuidInterface;

class GameCommandHandler implements ClientPacketHandlerInterface
{
    public function __construct(private readonly ClientPool $clientPool)
    {
    }

    public function handle(ResourceSocket $socket, UuidInterface $socketUuid, string ...$packetData): void
    {
        $rawCommand = implode (' ', $packetData);

        $client = $this->clientPool->getClientBySocketUuid($socketUuid->toString());

        if ($client) {
            /** @var PlayerCommandQueue $playerCommandQueue */
             $playerCommandQueue = $client->getPlayer()->getComponent(PlayerCommandQueue::class);
             $playerCommandQueue->getCommandQueue()->enqueue($rawCommand);
        }
//
//        $systems = Kernel::getAllRegisteredConcreteClassesFromInterface(ReceiverSystemInterface::class);
//
//        foreach ($systems as $system) {
//            if ($system instanceof ReceiverSystemInterface) {
//                $system->parse($rawCommand);
//            }
//        }
    }
}
