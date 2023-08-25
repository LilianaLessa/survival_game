<?php

declare(strict_types=1);

namespace App\System\Server\Client\Network;

use App\Engine\Component\ColorEffect;
use App\Engine\Component\DefaultColor;
use App\Engine\Component\HitPoints;
use App\Engine\Component\InGameName;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Component\MapViewPort;
use App\Engine\Entity\EntityManager;
use App\System\Event\Dispatcher;
use App\System\Event\Event\DebugMessageEvent;
use App\System\Event\Event\MapEntityRemoved;
use App\System\Event\Event\MapEntityUpdated;
use App\System\Event\Event\PlayerUpdated;
use App\System\Event\Event\UiMessageEvent;
use App\System\Event\Event\UpdatePlayerCurrentTarget;
use App\System\Server\ServerPacketHeader;
use App\System\World\WorldManager;

class ClientPool
{
    /** @var Client[] */
    private array $clients;

    /** @var Client[] */
    private array $clientsBySocketId;

    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly WorldManager $worldManager,
    )
    {
        $this->setUpUiMessageEventListener();
        $this->setUpDebugMessageEventListener();
        $this->setUpPlayerUpdatedEventListener();
        $this->setUpMapEntityUpdatedListener();
        $this->setUpMapEntityRemovedListener();
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
                        MapPosition::class,
                        MapViewPort::class,
                        HitPoints::class,
                        InGameName::class,
                    ));

                    $data = sprintf(
                        '%s %s',
                        ServerPacketHeader::PLAYER_UPDATED->value,
                        $message
                    );

                    /** @var Socket[] $uiMessageReceivers */
                    $uiMessageReceivers = array_filter(
                        $client->getSockets(),
                        fn ($s) => in_array($s->getSocketType(), [SocketType::UI_FIXED, SocketType::MAP])
                    );

                    foreach ($uiMessageReceivers as $socket) {
                        $socket->getSocket()->write($data);
                    }

                    //send map entities updates:
                    /**
                     * @var MapViewPort $playerViewport
                     * @var MapPosition $playerMapPosition
                     */
                    [$playerViewport, $playerMapPosition] = $entity->explode(
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
        );
    }

    private function setUpMapEntityUpdatedListener(): void
    {
        Dispatcher::getInstance()->addListener(
            MapEntityUpdated::EVENT_NAME,
            function (MapEntityUpdated $event) {
                $updatedEntity = $event->getEntity();
                /** @var ?MapPosition $entityPosition */
                $entityPosition = $updatedEntity->getComponent(MapPosition::class);

                $worldDimensions = $this->worldManager->getWorldDimensions();

                $message = serialize($updatedEntity->reduce(
                    MapPosition::class,
                    MapSymbol::class,
                    //todo any other drawable component
                    DefaultColor::class,
                    ColorEffect::class,
                    //todo any other color component
                ));

                $entityUpdatedData = sprintf(
                    '%s %s',
                    ServerPacketHeader::MAP_ENTITY_UPDATED->value,
                    $message
                );

                $entityRemovedData = sprintf(
                    '%s %s',
                    ServerPacketHeader::MAP_ENTITY_REMOVED->value,
                    $updatedEntity->getId()
                );

                foreach ($this->clients as $client) {
                    /** @var Socket[] $mapClients */
                    $mapClients = array_filter(
                        $client->getSockets(),
                        fn ($s) => $s->getSocketType() === SocketType::MAP
                    );
                    $player = $client->getPlayer();
                    if (!$player) {
                        continue;
                    }
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
                            foreach ($mapClients as $socket) {
                                $socket->getSocket()->write($entityUpdatedData);
                            }
                        }
                    } else {
                        foreach ($mapClients as $socket) {
                            $socket->getSocket()->write($entityRemovedData);
                        }
                    }
                }
            }
        );
    }

    private function setUpMapEntityRemovedListener(): void
    {
        Dispatcher::getInstance()->addListener(
            MapEntityRemoved::EVENT_NAME,
            function (MapEntityRemoved $event) {
                $data = sprintf(
                    '%s %s',
                    ServerPacketHeader::MAP_ENTITY_REMOVED->value,
                    $event->getEntityId()
                );

                foreach ($this->clients as $client) {
                    /** @var Socket[] $mapClients */
                    $mapClients = array_filter(
                        $client->getSockets(),
                        fn ($s) => $s->getSocketType() === SocketType::MAP
                    );

                    foreach ($mapClients as $socket) {
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
