<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\Socket\ResourceSocket;
use App\System\Server\Client\Network\ClientPool;
use App\System\Server\Client\Network\Socket;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ServerPacketHeader;
use Ramsey\Uuid\UuidInterface;

class AttachClientHandler implements ClientPacketHandlerInterface
{
    public function __construct(
        private readonly ClientPool $clientPool,
    ) {
    }

    public function handle(ResourceSocket $socket, UuidInterface $socketUuid, string ...$packetData): void
    {
       $clientUuid = $packetData[0] ?? '';
       $socketType = SocketType::tryFrom($packetData[1] ?? '');

       $client = $this->clientPool->getClientByUuid($clientUuid);

        $response = sprintf(
            "%s %s",
            ServerPacketHeader::CLIENT_REGISTER_FAILED->value,
            sprintf("Invalid request: %s.\n", implode(' ',$packetData))
        );

       if ($client && $socketType) {
           $client->addSocket(
               new Socket(
                   $socketUuid,
                   $socket,
                   $socketType
               )
           );

           $this->clientPool->updateClient($client);

           $response = sprintf(
               "%s %s",
               ServerPacketHeader::CLIENT_REGISTER_SUCCESS->value,
               sprintf("Client Registered. Type '%s'.\n", $socketType->value)
           );
       }

       $socket->write($response);
    }

}
