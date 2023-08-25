<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\Socket\ResourceSocket;
use App\System\Server\Client\Network\ClientPool;
use Ramsey\Uuid\UuidInterface;

class ShutdownSocketHandler implements ClientPacketHandlerInterface
{
    public function __construct(private readonly ClientPool $clientPool)
    {
    }

    public function handle(ResourceSocket $socket, UuidInterface $socketUuid, string ...$packetData): void
    {
        $client = $this->clientPool->getClientBySocketUuid($socketUuid->toString());

        if ($client) {
            $internalSocket = $client->getSocketByUuid($socketUuid->toString());
            $client->removeSocket($internalSocket);
            $this->clientPool->updateClient($client);
            $hasRemainingSockets = count($client->getSockets()) > 0;
            if (!$hasRemainingSockets) {
                $this->clientPool->removeClient($client);
            }
        }
    }
}
