<?php

declare(strict_types=1);

namespace App\System\Server\Client\Network;

use App\Engine\Entity\EntityManager;

class ClientPool
{
    /** @var Client[] */
    private array $clients = [];

    /** @var Client[] */
    private array $clientsBySocketId = [];

    public function __construct(
        private readonly EntityManager $entityManager,
    )
    {
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
            $socket->getSocket()->end();
            !$socket->getSocket()->isClosed() && $socket->getSocket()->close();
            unset($this->clientsBySocketId[$socket->getUuid()->toString()]);
        }

        $playerId = $client->getPlayer()->getId();
        $this->entityManager->removeEntity($playerId);

        $client->shutDown();

        unset($this->clients[$client->getUuid()->toString()]);
    }
}
