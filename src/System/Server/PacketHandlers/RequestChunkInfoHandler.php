<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\ByteStream\ClosedException;
use Amp\ByteStream\StreamException;
use Amp\Socket\ResourceSocket;
use App\System\Server\Client\Network\ClientPool;
use App\System\Server\ServerPacketHeader;
use App\System\World\WorldManager;
use Ramsey\Uuid\UuidInterface;

class RequestChunkInfoHandler implements ClientPacketHandlerInterface
{
    public function __construct(
        private readonly ClientPool $clientPool,
        private readonly WorldManager $worldManager
    ) {
    }

    public function handle(ResourceSocket $socket, UuidInterface $socketUuid, string ...$packetData): void
    {
        $colorData = $this->worldManager->getChunkBackgroundColorData(
            ...array_map(fn($cId) => (int)$cId, $packetData)
        );

        $message = serialize(
            $colorData
        );

        try {
            $socket->write(ServerPacketHeader::MAP_INFO_UPDATED->pack($message));
        } catch (ClosedException $e) {
        } catch (StreamException $e) {
        }
    }
}
