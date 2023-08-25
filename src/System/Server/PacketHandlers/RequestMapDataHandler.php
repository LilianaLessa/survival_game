<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\ByteStream\ClosedException;
use Amp\ByteStream\StreamException;
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
        $client = $this->clientPool->getClientBySocketUuid($socketUuid->toString());
        /** @var MapPosition $mapPosition */
        $mapPosition = $client?->getPlayer()->getComponent(MapPosition::class);
        if ($mapPosition) {
            $relevantChunkIds = $this->worldManager->getNearbyChunkIds($mapPosition->get());

            $colorData = $this->worldManager->getChunkBackgroundColorData(...$relevantChunkIds);

            //todo send also relevant chunk data.
            $message = serialize(
                [
                    $this->worldManager->getWorldDimensions(),
                    $colorData
                ]
            );


            try {
                $socket->write(ServerPacketHeader::MAP_INFO_UPDATED->pack($message));
            } catch (ClosedException $e) {
            } catch (StreamException $e) {
            }
        }
    }
}
