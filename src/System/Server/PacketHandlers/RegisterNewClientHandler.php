<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\Socket\ResourceSocket;
use App\System\Server\Client\Client;
use App\System\Server\Client\ClientPool;
use App\System\Server\Client\Socket;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\UuidInterface;

class RegisterNewClientHandler implements ClientPacketHandlerInterface
{
    public function __construct(private readonly ClientPool $clientPool)
    {
    }

    public function handle(ResourceSocket $socket, UuidInterface $socketUuid, string ...$packetData): void
    {
        $newClient = new Client(UuidV4::uuid4());

        $newClient->addSocket(
            new Socket(
                $socketUuid,
                $socket
            )
        );

        $this->clientPool->addClient($newClient);

        $response = sprintf(
            "%s %s",
            'register_new_client_response',
            $newClient->getUuid()->toString()
        );

        $socket->write($response);
    }
}
