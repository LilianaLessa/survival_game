<?php

declare(strict_types=1);

namespace App\System\Server\EventListener;

use App\Engine\Component\HitPoints;
use App\Engine\Component\InGameName;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\System\Event\Event\AbstractEvent;
use App\System\Event\Event\AbstractEventListener;
use App\System\Event\Event\UpdatePlayerCurrentTarget;
use App\System\Server\Client\Network\ClientPool;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ServerPacketHeader;

class UpdatePlayerCurrentTargetServerEventListener extends AbstractEventListener
{
    public function __construct(private readonly ClientPool $clientPool)
    {
        parent::__construct();
    }

    public function __invoke(AbstractEvent|UpdatePlayerCurrentTarget $event): void
    {
        $socketUuid = $event->getPlayerCommandQueue()->getSocketUuid();
        $client = $this->clientPool->getClientBySocketUuid($socketUuid) ?? null;

        if ($client) {
            $entity = $event->getCurrentTarget();

            $message = serialize($this->getReducedEntity($entity));

            $client->send(
                ServerPacketHeader::UI_CURRENT_TARGET_UPDATED->pack($message),
                SocketType::UI_FIXED
            );
        }
    }

    protected function getEventName(): string
    {
        return UpdatePlayerCurrentTarget::EVENT_NAME;
    }

    private function getReducedEntity($entity)
    {
        return $entity->reduce(
            MapSymbol::class,
            MapPosition::class,
            HitPoints::class,
            InGameName::class,
        );
    }
}
