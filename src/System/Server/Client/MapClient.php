<?php

declare(strict_types=1);

namespace App\System\Server\Client;

use App\Engine\Component\HitPoints;
use App\Engine\Component\InGameName;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Component\Monster;
use App\Engine\Entity\Entity;
use App\System\PresetLibrary\PresetDataType;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ClientPacketHeader;
use App\System\Server\ServerPacketHeader;
use App\System\Server\ServerPresetLibrary;

class MapClient extends AbstractClient
{

    public function start(): void
    {

    }

    protected function getSocketType(): SocketType
    {
        return SocketType::MAP;
    }
}

