<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\ByteStream\ClosedException;
use Amp\ByteStream\StreamException;
use Amp\Socket\ResourceSocket;
use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\EntityManager;
use App\System\Server\Client\Network\ClientPool;
use App\System\Server\EventListener\PlayerUpdatedServerEventListener;
use App\System\Server\ServerPacketHeader;
use Ramsey\Uuid\UuidInterface;

class RequestPlayerDataHandler implements ClientPacketHandlerInterface
{
    public function __construct(
        private readonly ClientPool $clientPool,
        private readonly EntityManager $entityManager,
        private readonly PlayerUpdatedServerEventListener $playerUpdatedServerEventListener,
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
            $message = serialize($this->playerUpdatedServerEventListener->getReducedEntity($entity));


            try {
                $socket->write(ServerPacketHeader::PLAYER_UPDATED->pack($message));
            } catch (ClosedException $e) {
            } catch (StreamException $e) {
            }
        }
    }
}
