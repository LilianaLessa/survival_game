<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\Socket\ResourceSocket;
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

        $playerEntity = null;

        /**
         * @var PlayerCommandQueue $playerCommandQueue
         */
        foreach ($players as $entityId => [$playerCommandQueue]) {
            $socketId = $playerCommandQueue->getSocketUuid();
            if (in_array($socketId, $clientSocketUuids)) {
                $playerEntity = $this->entityManager->getEntityById($entityId);
                break;
            }
        }

        if ($playerEntity) {
            $message = serialize($playerEntity);

            /** @var Socket[] $uiMessageReceivers */
            $uiMessageReceivers = array_filter(
                $clientSockets,
                fn ($s) => $s->getSocketType() === SocketType::UI_FIXED
            );

            $data = sprintf(
                '%s %s',
                ServerPacketHeader::UI_PLAYER_UPDATED->value,
                $message
            );

            foreach ($uiMessageReceivers as $socket) {
                $socket->getSocket()->write($data);
            }
        }
    }
}
