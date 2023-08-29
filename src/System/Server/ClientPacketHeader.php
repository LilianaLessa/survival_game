<?php

declare(strict_types=1);

namespace App\System\Server;

use App\System\Kernel;
use App\System\Server\PacketHandlers\AttachClientHandler;
use App\System\Server\PacketHandlers\ClientPacketHandlerInterface;
use App\System\Server\PacketHandlers\GameCommandHandler;
use App\System\Server\PacketHandlers\RegisterNewClientHandler;
use App\System\Server\PacketHandlers\RequestChunkInfoHandler;
use App\System\Server\PacketHandlers\RequestClientUuidHandler;
use App\System\Server\PacketHandlers\RequestMapDataHandler;
use App\System\Server\PacketHandlers\RequestPlayerDataHandler;
use App\System\Server\PacketHandlers\RequestPlayerSurroundingEntities;
use App\System\Server\PacketHandlers\SetPlayerNameHandler;
use App\System\Server\PacketHandlers\ShutdownSocketHandler;

enum ClientPacketHeader: string
{
    use PacketTrait;

    case ATTACH_CLIENT = 'attach_client';
    case REGISTER_NEW_CLIENT = 'register_new_client';
    case REQUEST_CLIENT_UUID = 'request_client_uuid';
    case GAME_COMMAND = 'game_command';

    case SHUTDOWN_SOCKET = 'exit';

    case REQUEST_PLAYER_DATA = 'request_player_data';

    case SET_PLAYER_NAME = 'set_player_name';

    case REQUEST_MAP_DATA = 'request_map_info';
    case REQUEST_CHUNK_INFO = 'request_chunk_info';

    case REQUEST_PLAYER_SURROUNDING_ENTITIES = 'request_player_surrounding_entities';

    public function getHandler(): ClientPacketHandlerInterface
    {
        return match ($this) {
            self::REGISTER_NEW_CLIENT => Kernel::getContainer()->get(RegisterNewClientHandler::class),
            self::REQUEST_CLIENT_UUID => Kernel::getContainer()->get(RequestClientUuidHandler::class),
            self::REQUEST_PLAYER_DATA => Kernel::getContainer()->get(RequestPlayerDataHandler::class),
            self::REQUEST_PLAYER_SURROUNDING_ENTITIES =>
                Kernel::getContainer()->get(RequestPlayerSurroundingEntities::class),
            self::REQUEST_MAP_DATA => Kernel::getContainer()->get(RequestMapDataHandler::class),
            self::REQUEST_CHUNK_INFO => Kernel::getContainer()->get(RequestChunkInfoHandler::class),
            self::ATTACH_CLIENT => Kernel::getContainer()->get(AttachClientHandler::class),
            self::GAME_COMMAND => Kernel::getContainer()->get(GameCommandHandler::class),
            self::SHUTDOWN_SOCKET => Kernel::getContainer()->get(ShutdownSocketHandler::class),
            self::SET_PLAYER_NAME => Kernel::getContainer()->get(SetPlayerNameHandler::class),
        };
    }
}
