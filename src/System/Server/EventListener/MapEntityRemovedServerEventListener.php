<?php

declare(strict_types=1);

namespace App\System\Server\EventListener;

use App\System\Event\Event\AbstractEvent;
use App\System\Event\Event\AbstractEventListener;
use App\System\Event\Event\MapEntityRemoved;
use App\System\Server\Client\Network\ClientPool;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ServerPacketHeader;

class MapEntityRemovedServerEventListener extends AbstractEventListener
{
    public function __construct(private readonly ClientPool $clientPool)
    {
        parent::__construct();
    }

    public function __invoke(AbstractEvent|MapEntityRemoved $event): void
    {
        $data = ServerPacketHeader::MAP_ENTITY_REMOVED->pack($event->getEntityId());

        foreach ($this->clientPool->getClients() as $client) {
            $client->send($data, SocketType::MAP);
        }
    }

    protected function getEventName(): string
    {
        return MapEntityRemoved::EVENT_NAME;
    }
}
