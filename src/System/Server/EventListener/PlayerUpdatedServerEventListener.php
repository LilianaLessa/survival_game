<?php

declare(strict_types=1);

namespace App\System\Server\EventListener;

use App\Engine\Component\HitPoints;
use App\Engine\Component\InGameName;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Component\MapViewPort;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Event\Dispatcher;
use App\System\Event\Event\AbstractEvent;
use App\System\Event\Event\AbstractEventListener;
use App\System\Event\Event\MapEntityRemoved;
use App\System\Event\Event\MapEntityUpdated;
use App\System\Event\Event\PlayerUpdated;
use App\System\Server\Client\Network\ClientPool;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ServerPacketHeader;
use App\System\World\WorldManager;

class PlayerUpdatedServerEventListener extends AbstractEventListener
{
    public function __construct(
        private readonly ClientPool $clientPool,
        private readonly EntityManager $entityManager,
        private readonly WorldManager $worldManager,
    )
    {
        parent::__construct();
    }

    public function __invoke(AbstractEvent|PlayerUpdated $event): void
    {
        $socketUuid = $event->getPlayerCommandQueue()->getSocketUuid();
        $client = $this->clientPool->getClientBySocketUuid($socketUuid) ?? null;

        if ($client) {
            $player = $event->getPlayerEntity();

            $message = serialize($this->getReducedEntity($player));
            $data = ServerPacketHeader::PLAYER_UPDATED->pack($message);

            $client->send(
                $data,
                SocketType::UI_FIXED,
                SocketType::MAP
            );

            $this->sendMapEntitiesUpdates($player);
        }
    }

    protected function getEventName(): string
    {
        return PlayerUpdated::EVENT_NAME;
    }

    public function getReducedEntity(Entity $entity): Entity
    {
        return $entity->reduce(
            MapSymbol::class,
            MapPosition::class,
            MapViewPort::class,
            HitPoints::class,
            InGameName::class,
        );
    }

    private function sendMapEntitiesUpdates(Entity $player): void
    {
        /**
         * @var MapViewPort $playerViewport
         * @var MapPosition $playerMapPosition
         */
        [$playerViewport, $playerMapPosition] = $player->explode(
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
        foreach ($mapEntities as $mapEntityId => [$entityMapPosition]) {
            $inViewport = $playerViewport->isPointInBoundaries(
                $entityMapPosition->get(),
                $playerMapPosition,
                $worldDimensions,
            );

            if ($inViewport) {
                $mapEntity = $this->entityManager->getEntityById($mapEntityId);
                Dispatcher::dispatch(new MapEntityUpdated($mapEntity));
            } else {
                //todo only relevant entities for player.
                Dispatcher::dispatch(new MapEntityRemoved($mapEntityId));
            }
        }
    }
}
