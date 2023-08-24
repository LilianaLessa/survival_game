<?php

declare(strict_types=1);

namespace App\System\Server\Client;

use App\System\PresetLibrary\PresetDataType;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ClientPacketHeader;
use App\System\Server\ServerPacketHeader;
use App\System\Server\ServerPresetLibrary;
use function sprintf;

class MainClient extends AbstractClient
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
        $this->socket->write(ClientPacketHeader::REQUEST_CLIENT_UUID->value);

        while($this->socket->isWritable() && $this->socket->isReadable()) {
            $rawPackageData = $this->socket->read();
            if ($rawPackageData) {
                [$packageHeader, $packageData] = $this->parsePackage($rawPackageData);
                if ($packageHeader) {
                    $this->processNextMessage($packageHeader, ...$packageData);
                }
            }
        }

        echo "\n\nConnection closed. GoodBye!\n\n";
    }

    protected function register(SocketType $socketType, ?string $clientUuid): string
    {
        $this->socket->write(ClientPacketHeader::REGISTER_NEW_CLIENT->value);

        return $this->socket->read();
    }

    protected function getSocketType(): SocketType
    {
        return SocketType::MAIN;
    }

    private function processNextMessage(ServerPacketHeader $serverPacketHeader, string ...$packetData): void
    {
        $this->printPackageInfo($serverPacketHeader, $packetData);

        switch ($serverPacketHeader) {
            case ServerPacketHeader::CLIENT_ID:
                $this->initPlayerName();
                $this->initChildClients($packetData[0] ?? null);
                break;
            default:
                break;
        }
    }

    private function initChildClients(?string $clientUuid): void
    {
        //todo this part is POC, ubuntu specific, fix it.
        if ($clientUuid) {
            /** @var SocketType[] $socketTypes */
            $socketTypes = SocketType::cases();

            echo "\n\n";
            foreach ($socketTypes as $socketType) {
                if ($socketType !== SocketType::MAIN) {
                    $openClientCommand = sprintf(
                        'gnome-terminal -- php8.2 client_ubuntu.php %s %s',
                        $clientUuid,
                        $socketType->value
                    );
                    echo $openClientCommand . "\n";
                    system($openClientCommand);
                }
            }
        }
    }

    private function initPlayerName():void
    {
        echo "\n\n";
        $name = readline("If you want, type a player name: ");

        if ($name) {
            $this->socket->write(sprintf(
                '%s %s',
                ClientPacketHeader::SET_PLAYER_NAME->value,
                $name
            ));
        }
    }
}
