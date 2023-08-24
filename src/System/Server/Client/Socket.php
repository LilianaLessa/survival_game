<?php

declare(strict_types=1);

namespace App\System\Server\Client;

use Amp\Socket\ResourceSocket;
use Ramsey\Uuid\UuidInterface;

class Socket
{
    public function __construct(private readonly UuidInterface $uuid, private readonly ResourceSocket $socket)
    {
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getSocket(): ResourceSocket
    {
        return $this->socket;
    }
}
