<?php

declare(strict_types=1);

namespace App\System\Server\Client;

use App\System\PresetLibrary\PresetDataType;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ClientPacketHeader;
use App\System\Server\ServerPacketHeader;
use App\System\Server\ServerPresetLibrary;
class CliClient extends AbstractClient
{
    public function __construct(ServerPresetLibrary $serverPresetLibrary)
    {
        [ $mapServer ] = $serverPresetLibrary->getPresetByNameAndTypes(
            'main',
            PresetDataType::SERVER_CONFIG
        );

        parent::__construct($mapServer);
    }

    public function start(): void
    {
        $this->socket->write(
            sprintf('%s', ClientPacketHeader::REQUEST_CLIENT_UUID->value)
        );

        $rawPackageData = $this->socket->read();
        if ($rawPackageData) {
            $this->printPackageInfo(
                ...$this->parsePackage($rawPackageData)
            );

            while ($this->socket->isWritable() && $this->socket->isReadable()) {
                $command = readline('>>');
                $this->socket->write(
                    sprintf('%s %s', ClientPacketHeader::GAME_COMMAND->value, $command)
                );
            }
        }

        echo "\n\nConnection closed. GoodBye!\n\n";
    }

    protected function getSocketType(): SocketType
    {
        return SocketType::CLI;
    }
}
