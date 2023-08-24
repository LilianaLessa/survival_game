<?php

declare(strict_types=1);

namespace App\System\Server;

use App\System\Kernel;
use App\System\Server\PacketHandlers\ClientPacketHandlerInterface;
use App\System\Server\PacketHandlers\RegisterNewClientHandler;

enum ClientPacketHeader: string
{
    //case REGISTER_CLIENT = 'register_client';
    case REGISTER_NEW_CLIENT = 'register_new_client';

    public function getHandler(): ClientPacketHandlerInterface
    {
        return match ($this) {
            self::REGISTER_NEW_CLIENT => Kernel::getContainer()->get(RegisterNewClientHandler::class),
        };
    }
}
