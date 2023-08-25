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
    use PacketTrait;

    case CLIENT_REGISTER_FAILED = 'client_register_failed';
    case INVALID_REQUEST = 'invalid_request';
    case CLIENT_REGISTER_SUCCESS = 'client_register_success';
    case CLIENT_ID = 'client_id';

    case UI_MESSAGE = 'ui_message';
    case PLAYER_UPDATED = 'player_updated';
    case UI_CURRENT_TARGET_UPDATED = 'ui_current_target_updated';
    case UI_NEARBY_PLAYER_EXISTS = 'ui_nearby_player_exists';
    case UI_NEARBY_PLAYER_REMOVED = 'ui_nearby_player_removed';
    case DEBUG_MESSAGE = 'debug_message';

    case MAP_ENTITY_UPDATED = 'map_entity_updated';

    case MAP_ENTITY_REMOVED = 'map_entity_removed';

    case MAP_INFO_UPDATED = 'map_dimensions_updated';
}
