<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\Socket\ResourceSocket;
use App\Engine\Component\InGameName;
use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Server\Client\Network\ClientPool;
use Ramsey\Uuid\UuidInterface;

class SetPlayerNameHandler implements ClientPacketHandlerInterface
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

        /** @var ?Entity $entity */
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

        $newName = $packetData[0] ?? null;
        if ($entity && $newName) {
            $this->entityManager->updateEntityComponents(
                $entityId,
                new InGameName($newName)
            );
        }
    }
}
