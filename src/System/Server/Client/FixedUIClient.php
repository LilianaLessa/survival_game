<?php

declare(strict_types=1);

namespace App\System\Server\Client;

use App\Engine\Component\HitPoints;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapSymbol;
use App\Engine\Entity\Entity;
use App\System\PresetLibrary\PresetDataType;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ClientPacketHeader;
use App\System\Server\ServerPacketHeader;
use App\System\Server\ServerPresetLibrary;

class FixedUIClient extends AbstractClient
{
    private ?Entity $player;

    private string|false $clientIdString;

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
            ob_start();
            $this->printPackageInfo(
                ...$this->parsePackage($rawPackageData)
            );

            $this->clientIdString = ob_get_flush();

            $this->socket->write(
                sprintf('%s', ClientPacketHeader::REQUEST_PLAYER_DATA->value)
            );

            while($this->socket->isWritable() && $this->socket->isReadable()) {
                $rawPackageData = $this->socket->read();
                if ($rawPackageData) {
                    [$packageHeader, $packageData] = $this->parsePackage($rawPackageData);
                    if ($packageHeader) {
                        $this->processNextMessage($packageHeader, ...$packageData);
                    }
                }
            }
        }

        echo "\n\nConnection closed. GoodBye!\n\n";
    }

    private function processNextMessage(ServerPacketHeader $serverPacketHeader, string ...$packetData): void
    {
        $message = implode(' ', $packetData);

        $needUpdate = false;
        switch ($serverPacketHeader) {
            case ServerPacketHeader::UI_PLAYER_UPDATED:
                $this->player = unserialize($message);
                $needUpdate = true;
                break;
            default:
                break;
        }

        $needUpdate && $this->updateUi();
    }

    protected function getSocketType(): SocketType
    {
        return SocketType::UI_FIXED;
    }

    private function updateUi(): void
    {
        system('clear');

        ob_start();

        if ($this->clientIdString) {
            echo $this->clientIdString . "\n\n";
        }

        if ($this->player) {
            /** @var MapSymbol $mapSymbol */
            $mapSymbol = $this->player->getComponent(MapSymbol::class);

            /** @var HitPoints $hitPoints */
            $hitPoints = $this->player->getComponent(HitPoints::class);

            echo sprintf(
                "%s - HP: %d/%d - %s\n",
                $mapSymbol->getSymbol(),
                $hitPoints->getCurrent(),
                $hitPoints->getTotal(),
                sprintf(
                    '<todo player name - %s>',
                    $this->player->getId()
                ),
            );

            /** @var MapPosition $mapPosition */
            $mapPosition = $this->player->getComponent(MapPosition::class);

            echo sprintf(
                "Position: %d %d\n",
                $mapPosition->get()->getX(),
                $mapPosition->get()->getY(),
            );
        }

        ob_end_flush();
    }
}

