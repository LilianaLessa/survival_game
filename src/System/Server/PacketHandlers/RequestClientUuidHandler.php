<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\Socket\ResourceSocket;
use App\System\Server\Client\Network\ClientPool;
use App\System\Server\ServerPacketHeader;
use Ramsey\Uuid\UuidInterface;

class RequestClientUuidHandler implements ClientPacketHandlerInterface
{
    public function __construct(
        private readonly ClientPool $clientPool,
    ) {
    }

    public function handle(ResourceSocket $socket, UuidInterface $socketUuid, string ...$packetData): void
    {
        $client = $this->clientPool->getClientBySocketUuid($socketUuid->toString());
        $response = ServerPacketHeader::INVALID_REQUEST->pack(
            sprintf("Could not retrieve client uuid: %s", implode(' ', $packetData))
        );

        if ($client) {
            $response = ServerPacketHeader::CLIENT_ID->pack($client->getUuid()->toString());
        }

        $socket->write($response);
    }
}
