<?php

declare(strict_types=1);

namespace App\System\Server\EventListener;

use App\Engine\Component\MapPosition;
use App\System\Event\Event\AbstractEvent;
use App\System\Event\Event\AbstractEventListener;
use App\System\Event\Event\PlayerChunkUpdated;
use App\System\Server\Client\Network\ClientPool;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ServerPacketHeader;
use App\System\World\WorldManager;

class PlayerChunkUpdatedServerEventListener extends AbstractEventListener
{
    public function __construct(
        private readonly ClientPool $clientPool,
        private readonly WorldManager $worldManager,
    )
    {
        parent::__construct();
    }

    public function __invoke(AbstractEvent|PlayerChunkUpdated $event): void
    {
        $socketUuid = $event->getPlayerCommandQueue()->getSocketUuid();
        $client = $this->clientPool->getClientBySocketUuid($socketUuid) ?? null;

        /** @var MapPosition $mapPosition */
        $mapPosition = $client?->getPlayer()->getComponent(MapPosition::class);

        if ($mapPosition) {
            $relevantChunkIds = $this->worldManager->getNearbyChunkIds($mapPosition->get());

            $colorData = $this->worldManager->getChunkBackgroundColorData(...$relevantChunkIds);

            $message = serialize(
                [
                    $this->worldManager->getWorldDimensions(),
                    $colorData
                ]
            );

            $client->send(
                ServerPacketHeader::MAP_INFO_UPDATED->pack($message),
                SocketType::MAP
            );
        }
    }

    protected function getEventName(): string
    {
        return PlayerChunkUpdated::EVENT_NAME;
    }
}
