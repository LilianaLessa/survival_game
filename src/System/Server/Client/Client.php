<?php

declare(strict_types=1);

namespace App\System\Server\Client;

use Ramsey\Uuid\UuidInterface;

class Client
{
    /** @var Socket[] */
    private array $sockets;

    public function __construct(private readonly UuidInterface $uuid)
    {
         $this->sockets = [];
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getSockets(): array
    {
        return $this->sockets;
    }

    public function getSocketByUuid(string $uuid): ?Socket
    {
        return $this->sockets[$uuid] ?? null;
    }

    public function removeSocket(string $uuid): void
    {
        unset($this->sockets[$uuid]);
    }

    public function addSocket(Socket $socket): void
    {
        $this->sockets[$socket->getUuid()->toString()] = $socket;
    }
}
