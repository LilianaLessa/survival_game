<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\Socket\ResourceSocket;
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
        $data = implode (' ', $packetData);

        $systems = Kernel::getAllRegisteredConcreteClassesFromInterface(ReceiverSystemInterface::class);

        foreach ($systems as $system) {
            if ($system instanceof ReceiverSystemInterface) {
                $system->parse($data);
            }
        }
    }
}
