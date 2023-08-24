<?php

declare(strict_types=1);

namespace App\System\Server\Client;

class ClientPool
{
    /** @var Client[] */
    private array $clients;

    public function getClients(): array
    {
        return $this->clients;
    }
}
