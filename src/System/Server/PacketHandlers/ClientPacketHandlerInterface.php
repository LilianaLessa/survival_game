<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\Socket\ResourceSocket;
use Ramsey\Uuid\UuidInterface;

interface ClientPacketHandlerInterface
{
    public function handle(ResourceSocket $socket, UuidInterface $socketUuid, string ...$packetData): void;
}
