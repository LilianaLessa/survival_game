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
use Ramsey\Uuid\UuidInterface;

class RequestPlayerDataHandler implements ClientPacketHandlerInterface
{
    public function __construct(
        private readonly ClientPool $clientPool,
        private readonly EntityManager $entityManager
    ) {
    }

    public function handle(ResourceSocket $socket, UuidInterface $socketUuid, string ...$packetData): void
    {
        $client = $this->clientPool->getClientBySocketUuid($socketUuid->toString());
        $clientSockets = $client->getSockets();
        $clientSocketUuids = array_map(fn($s) => $s->getUuid()->toString(), $clientSockets);

        $players = $this->entityManager->getEntitiesWithComponents(
            PlayerCommandQueue::class,
        );

        $entity = null;

        /**
         * @var PlayerCommandQueue $playerCommandQueue
         */
        foreach ($players as $entityId => [$playerCommandQueue]) {
            $socketId = $playerCommandQueue->getSocketUuid();
            if (in_array($socketId, $clientSocketUuids)) {
                $entity = $this->entityManager->getEntityById($entityId);
                break;
            }
        }

        if ($entity) {
            //todo this is repeated at \App\System\Server\Client\Network\ClientPool::setUpPlayerUpdatedEventListener
            $message = serialize($entity->reduce(
                MapSymbol::class,
                MapPosition::class,
                MapViewPort::class,
                HitPoints::class,
                InGameName::class,
            ));

            $data = sprintf(
                '%s %s',
                ServerPacketHeader::PLAYER_UPDATED->value,
                $message
            );

            $socket->write($data);
        }
    }
}
