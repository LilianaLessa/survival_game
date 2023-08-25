<?php

declare(strict_types=1);

namespace App\System\Server\EventListener;

use App\System\Event\Event\AbstractEvent;
use App\System\Event\Event\AbstractEventListener;
use App\System\Event\Event\DebugMessageEvent;
use App\System\Server\Client\Network\ClientPool;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ServerPacketHeader;

class DebugMessageServerEventListener extends AbstractEventListener
{
    public function __construct(private readonly ClientPool $clientPool)
    {
        parent::__construct();
    }

    public function __invoke(AbstractEvent|DebugMessageEvent $event): void
    {
        $message = $event->getMessage();
        $socketUuid = $event->getPlayerCommandQueue()->getSocketUuid();
        $client = $this->clientPool->getClientBySocketUuid($socketUuid) ?? null;

        if ($client) {
            $client->send(
                ServerPacketHeader::DEBUG_MESSAGE->pack($message),
                SocketType::MAIN
            );
        }
    }

    protected function getEventName(): string
    {
        return DebugMessageEvent::EVENT_NAME;
    }
}
