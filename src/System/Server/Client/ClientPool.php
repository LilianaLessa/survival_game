<?php

declare(strict_types=1);

namespace App\System\Server\Client;

class ClientPool
{
    /** @var Client[] */
    private array $clients;

    /** @var Client[] */
    private array $clientsBySocketId;

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
}
