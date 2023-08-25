<?php

declare(strict_types=1);

namespace App\System\Server\EventListener;

use App\Engine\Component\ColorEffect;
use App\Engine\Component\DefaultColor;
use App\Engine\Component\HitPoints;
use App\Engine\Component\InGameName;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Component\MapViewPort;
use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\Entity;
use App\System\Event\Event\AbstractEvent;
use App\System\Event\Event\AbstractEventListener;
use App\System\Event\Event\MapEntityUpdated;
use App\System\Server\Client\Network\ClientPool;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ServerPacketHeader;
use App\System\World\WorldManager;

class MapEntityUpdatedServerEventListener extends AbstractEventListener
{
    public function __construct(
        private readonly ClientPool $clientPool,
        private readonly WorldManager $worldManager,
    )
    {
        parent::__construct();
    }

    public function __invoke(AbstractEvent|MapEntityUpdated $event): void
    {
        $updatedEntity = $event->getEntity();
        /** @var ?MapPosition $entityPosition */
        /** @var ?PlayerCommandQueue $playerCommandQueue */
        [
            $entityPosition,
            $playerCommandQueue
        ]= $updatedEntity->explode(
            MapPosition::class,
            PlayerCommandQueue::class
        );

        $worldDimensions = $this->worldManager->getWorldDimensions();

        $message = serialize($this->getReducedEntity($updatedEntity));

        $entityUpdatedData = ServerPacketHeader::MAP_ENTITY_UPDATED->pack($message);
        $entityRemovedData = ServerPacketHeader::MAP_ENTITY_REMOVED->pack($updatedEntity->getId());
        $nearbyPlayerExists = ServerPacketHeader::UI_NEARBY_PLAYER_EXISTS->pack($message);
        $nearbyPlayerExistsRemoved = ServerPacketHeader::UI_NEARBY_PLAYER_REMOVED->pack($updatedEntity->getId());

        $mapData = $entityRemovedData;
        $uiData = $nearbyPlayerExistsRemoved;

        foreach ($this->clientPool->getClients() as $client) {
            $player = $client->getPlayer();

            if ($player) {
                /**
                 * @var MapPosition $playerMapPosition
                 * @var MapViewPort $playerViewport
                 */
                [$playerMapPosition, $playerViewport] = $player->explode(
                    MapPosition::class,
                    MapViewPort::class
                );

                if ($entityPosition) {
                    $inViewport = $playerViewport->isPointInBoundaries(
                        $entityPosition->get(),
                        $playerMapPosition,
                        $worldDimensions,
                    );

                    if ($inViewport) {
                        $mapData = $entityUpdatedData;
                        $uiData = $nearbyPlayerExists;
                    }
                }

                $client->send($mapData, SocketType::MAP);
                if ($player->getId() !== $updatedEntity->getId()) {
                    $playerCommandQueue && $client->send($uiData, SocketType::UI_FIXED);
                }
            }
        }
    }

    protected function getEventName(): string
    {
        return MapEntityUpdated::EVENT_NAME;
    }

    //todo move it to factory.
    public function getReducedEntity(Entity $updatedEntity): Entity
    {
        return $updatedEntity->reduce(
            MapPosition::class,
            MapSymbol::class,
            HitPoints::class,
            InGameName::class,
            //todo any other drawable component
            DefaultColor::class,
            ColorEffect::class,
            //todo any other color component
        );
    }
}
