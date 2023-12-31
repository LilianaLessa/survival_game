<?php

declare(strict_types=1);

namespace App\System\Server\Client;

use App\System\PresetLibrary\PresetDataType;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ClientPacketHeader;
use App\System\Server\ServerPacketHeader;
use App\System\Server\ServerPresetLibrary;

class UiMessageReceiverClient extends AbstractClient
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
            ClientPacketHeader::REQUEST_CLIENT_UUID->pack()
        );

        $rawPackageData = $this->readSocket();
        if ($rawPackageData) {
            $this->printPacketInfo(
                ...$this->parsePacket($rawPackageData)
            );

            echo "\n\n";

            while ($this->socket->isWritable() && $this->socket->isReadable()) {
                $rawPacketData = ServerPacketHeader::getPackets($this->readSocket());
                foreach ($rawPacketData as $rawPacket) {
                    [$packetHeader, $packet] = $this->parsePacket($rawPacket);
                    if ($packetHeader) {
                        $this->processNextMessage($packetHeader, ...$packet);
                    }
                }
            }
        }

        echo "\n\nConnection closed. GoodBye!\n\n";
    }

    private function processNextMessage(ServerPacketHeader $serverPacketHeader, string ...$packetData): void
    {
        $message = implode(' ', $packetData);

        switch ($serverPacketHeader) {
            case ServerPacketHeader::UI_MESSAGE:
                echo $message;
                break;
            default:
                break;
        }
    }

    protected function getSocketType(): SocketType
    {
        return SocketType::UI_MESSAGE_RECEIVER;
    }
}

