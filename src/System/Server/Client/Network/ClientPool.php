<?php

declare(strict_types=1);

namespace App\System\Server\Client\Network;

use App\Engine\Component\HitPoints;
use App\Engine\Component\InGameName;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Component\Monster;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\Event\Dispatcher;
use App\System\Event\Event\DebugMessageEvent;
use App\System\Event\Event\PlayerUpdated;
use App\System\Event\Event\UiMessageEvent;
use App\System\Event\Event\UpdatePlayerCurrentTarget;
use App\System\Server\ServerPacketHeader;

class ClientPool
{
    /** @var Client[] */
    private array $clients;

    /** @var Client[] */
    private array $clientsBySocketId;

    public function __construct(private readonly EntityManager $entityManager)
    {
        $this->setUpUiMessageEventListener();
        $this->setUpDebugMessageEventListener();
        $this->setUpPlayerUpdatedEventListener();
        $this->setUpUpdatePlayerCurrentTargetEventListener();
    }

    public function getClients(): array
    {
        return $this->clients;
    }

    public function addClient(Client $client): void
    {
        $this->updateClient($client);
    }

    public function updateClient(Client $client): void
    {
        $this->clients[$client->getUuid()->toString()] = $client;

        $sockets = $client->getSockets();
        foreach ($sockets as $socket) {
            $this->clientsBySocketId[$socket->getUuid()->toString()] = $client;
        }
    }

    public function getClientByUuid(string $clientUuid): ?Client
    {
        return $this->clients[$clientUuid] ?? null;
    }

    public function getClientBySocketUuid(string $socketUuid): ?Client
    {
        return $this->clientsBySocketId[$socketUuid] ?? null;
    }

    public function removeClient(Client $client): void
    {
        //remove all sockets related to client,
        $sockets = $client->getSockets();
        foreach ($sockets as $socket) {
            $socket->getSocket()->close();
            unset($this->clientsBySocketId[$socket->getUuid()->toString()]);
        }

        $playerId = $client->getPlayer()->getId();
        $this->entityManager->removeEntity($playerId);

        $client->shutDown();

        unset($this->clients[$client->getUuid()->toString()]);
    }

    private function setUpUiMessageEventListener(): void
    {
        Dispatcher::getInstance()->addListener(
            UiMessageEvent::EVENT_NAME,
            function (UiMessageEvent $event) {
               $message = $event->getMessage();
               $socketUuid = $event->getPlayerCommandQueue()->getSocketUuid();
               $client = $this->clientsBySocketId[$socketUuid] ?? null;

               if ($client) {
                   /** @var Socket[] $uiMessageReceivers */
                   $uiMessageReceivers = array_filter(
                       $client->getSockets(),
                       fn ($s) => $s->getSocketType() === SocketType::UI_MESSAGE_RECEIVER
                   );

                   $data = sprintf(
                       '%s %s',
                       ServerPacketHeader::UI_MESSAGE->value,
                       $message
                   );

                   foreach ($uiMessageReceivers as $socket) {
                       $socket->getSocket()->write($data);
                   }
               }
            }
        );
    }

    private function setUpDebugMessageEventListener(): void
    {
        Dispatcher::getInstance()->addListener(
            DebugMessageEvent::EVENT_NAME,
            function (DebugMessageEvent $event) {
                $message = $event->getMessage();
                $socketUuid = $event->getPlayerCommandQueue()->getSocketUuid();
                $client = $this->clientsBySocketId[$socketUuid] ?? null;

                if ($client) {
                    /** @var Socket[] $uiMessageReceivers */
                    $uiMessageReceivers = array_filter(
                        $client->getSockets(),
                        fn ($s) => $s->getSocketType() === SocketType::MAIN
                    );

                    $data = sprintf(
                        '%s %s',
                        ServerPacketHeader::DEBUG_MESSAGE->value,
                        $message
                    );

                    foreach ($uiMessageReceivers as $socket) {
                        $socket->getSocket()->write($data);
                    }
                }
            }
        );
    }

    //todo repeated at \App\System\Server\PacketHandlers\RequestPlayerDataHandler::handle
    private function setUpPlayerUpdatedEventListener(): void
    {
        Dispatcher::getInstance()->addListener(
            PlayerUpdated::EVENT_NAME,
            function (PlayerUpdated $event) {
                $socketUuid = $event->getPlayerCommandQueue()->getSocketUuid();
                $client = $this->clientsBySocketId[$socketUuid] ?? null;

                if ($client) {
                    $entity = $event->getPlayerEntity();

                    $message = serialize($entity->reduce(
                        MapSymbol::class,
                        HitPoints::class,
                        InGameName::class,
                        MapPosition::class,
                    ));

                    $data = sprintf(
                        '%s %s',
                        ServerPacketHeader::UI_PLAYER_UPDATED->value,
                        $message
                    );

                    /** @var Socket[] $uiMessageReceivers */
                    $uiMessageReceivers = array_filter(
                        $client->getSockets(),
                        fn ($s) => $s->getSocketType() === SocketType::UI_FIXED
                    );

                    foreach ($uiMessageReceivers as $socket) {
                        $socket->getSocket()->write($data);
                    }
                }
            }
        );
    }

    private function setUpUpdatePlayerCurrentTargetEventListener(): void
    {
        Dispatcher::getInstance()->addListener(
            UpdatePlayerCurrentTarget::EVENT_NAME,
            function (UpdatePlayerCurrentTarget $event) {
                $socketUuid = $event->getPlayerCommandQueue()->getSocketUuid();
                $client = $this->clientsBySocketId[$socketUuid] ?? null;

                if ($client) {
                    $entity = $event->getCurrentTarget();

                    $message = serialize($entity->reduce(
                        MapSymbol::class,
                        HitPoints::class,
                        InGameName::class,
                        MapPosition::class,
                    ));

                    /** @var Socket[] $uiMessageReceivers */
                    $uiMessageReceivers = array_filter(
                        $client->getSockets(),
                        fn ($s) => $s->getSocketType() === SocketType::UI_FIXED
                    );

                    $data = sprintf(
                        '%s %s',
                        ServerPacketHeader::UI_CURRENT_TARGET_UPDATED->value,
                        $message
                    );

                    foreach ($uiMessageReceivers as $socket) {
                        $socket->getSocket()->write($data);
                    }
                }
            }
        );
    }
}
