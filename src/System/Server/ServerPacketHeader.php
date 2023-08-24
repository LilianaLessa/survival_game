<?php

declare(strict_types=1);

namespace App\System\Server;

use App\System\Kernel;
use App\System\Server\PacketHandlers\AttachClientHandler;
use App\System\Server\PacketHandlers\ClientPacketHandlerInterface;
use App\System\Server\PacketHandlers\GameCommandHandler;
use App\System\Server\PacketHandlers\RegisterNewClientHandler;
use App\System\Server\PacketHandlers\ServerPacketHandlerInterface;
use App\System\Server\PacketHandlers\ShutdownSocketHandler;

enum ServerPacketHeader: string
{
    case SYSTEM_MESSAGE_INFO = 'system_message_info';
    case CLIENT_REGISTER_FAILED = 'client_register_failed';
    case INVALID_REQUEST = 'invalid_request';
    case CLIENT_REGISTER_SUCCESS = 'client_register_success';
    case CLIENT_ID = 'client_id';

    case UI_MESSAGE = 'ui_message';
    case UI_PLAYER_UPDATED = 'ui_player_updated';
    case UI_CURRENT_TARGET_UPDATED = 'ui_current_target_updated';
    case DEBUG_MESSAGE = 'debug_message';

    public function getHandler(): ServerPacketHandlerInterface
    {
//        return match ($this) {
//            //self::SYSTEM_MESSAGE => Kernel::getContainer()->get(RegisterNewClientHandler::class),
//        };
    }
}
