<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\Socket\ResourceSocket;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapViewPort;
use App\Engine\Entity\EntityManager;
use App\System\Event\Dispatcher;
use App\System\Event\Event\MapEntityUpdated;
use App\System\Server\Client\Network\ClientPool;
use App\System\World\WorldManager;
use Ramsey\Uuid\UuidInterface;

class RequestPlayerSurroundingEntities implements ClientPacketHandlerInterface
{
    public function __construct(
        private readonly ClientPool $clientPool,
        private readonly EntityManager $entityManager,
        private readonly WorldManager $worldManager,
    ) {
    }

    public function handle(ResourceSocket $socket, UuidInterface $socketUuid, string ...$packetData): void
    {
        $client = $this->clientPool->getClientBySocketUuid($socketUuid->toString());

        $playerEntity = $client->getPlayer();

        /**
         * @var MapViewPort $playerViewport
         * @var MapPosition $playerMapPosition
         */
        [$playerViewport, $playerMapPosition] = $playerEntity->explode(
            MapViewPort::class,
            MapPosition::class,
        );
        $worldDimensions = $this->worldManager->getWorldDimensions();

        $mapEntities = $this->entityManager->getEntitiesWithComponents(
            MapPosition::class
        );
        /**
         * @var MapPosition $entityMapPosition
         */
        foreach ($mapEntities as $mapEntityId => [ $entityMapPosition ]) {
            $inViewport = $playerViewport->isPointInBoundaries(
                $entityMapPosition->get(),
                $playerMapPosition,
                $worldDimensions,
            );

            if ($inViewport) {
                $mapEntity = $this->entityManager->getEntityById($mapEntityId);
                Dispatcher::dispatch(new MapEntityUpdated($mapEntity));
            }
        }
    }
}
