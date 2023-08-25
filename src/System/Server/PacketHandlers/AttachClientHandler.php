<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\ByteStream\ClosedException;
use Amp\ByteStream\StreamException;
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

        $response = ServerPacketHeader::CLIENT_REGISTER_FAILED->pack(
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

           $response = ServerPacketHeader::CLIENT_REGISTER_SUCCESS->pack(
               sprintf("Client Registered. Type '%s'.\n", $socketType->value)
           );
       }

        try {
            $socket->write($response);
        } catch (ClosedException $e) {
        } catch (StreamException $e) {
        }
    }
}
