<?php

declare(strict_types=1);

namespace App\System\Server\EventListener;

use App\System\Event\Event\AbstractEvent;
use App\System\Event\Event\AbstractEventListener;
use App\System\Event\Event\UiMessageEvent;
use App\System\Server\Client\Network\ClientPool;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ServerPacketHeader;

class UiMessageServerEventListener extends AbstractEventListener
{
    public function __construct(private readonly ClientPool $clientPool)
    {
        parent::__construct();
    }

    public function __invoke(AbstractEvent|UiMessageEvent $event): void
    {
        $message = $event->getMessage();
        $socketUuid = $event->getPlayerCommandQueue()->getSocketUuid();
        $client = $this->clientPool->getClientBySocketUuid($socketUuid) ?? null;

        if ($client) {
            $client->send(
                ServerPacketHeader::UI_MESSAGE->pack($message),
                SocketType::UI_MESSAGE_RECEIVER
            );
        }
    }

    protected function getEventName(): string
    {
        return UiMessageEvent::EVENT_NAME;
    }
}
