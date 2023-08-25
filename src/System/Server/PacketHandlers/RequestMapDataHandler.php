<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\Socket\ResourceSocket;
use App\Engine\Component\HitPoints;
use App\Engine\Component\InGameName;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Component\MapViewPort;
use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\EntityManager;
use App\System\Server\Client\Network\ClientPool;
use App\System\Server\Client\Network\Socket;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ServerPacketHeader;
use App\System\World\WorldManager;
use Ramsey\Uuid\UuidInterface;

class RequestMapDataHandler implements ClientPacketHandlerInterface
{
    public function __construct(
        private readonly ClientPool $clientPool,
        private readonly WorldManager $worldManager
    ) {
    }

    public function handle(ResourceSocket $socket, UuidInterface $socketUuid, string ...$packetData): void
    {
        //todo send also relevant chunk data.
        $message = serialize($this->worldManager->getWorldDimensions());

        $data = sprintf(
            '%s %s',
            ServerPacketHeader::MAP_INFO_UPDATED->value,
            $message
        );

        $socket->write($data);
    }
}
