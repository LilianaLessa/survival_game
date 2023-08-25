<?php

declare(strict_types=1);

namespace App\System\Server\Client;

use App\Engine\Component\HitPoints;
use App\Engine\Component\InGameName;
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
    private ?Entity $player = null;
    /** @var Entity[] */
    private array $currentTargets = [];

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
            $this->printPacketInfo(
                ...$this->parsePacket($rawPackageData)
            );

            $this->clientIdString = ob_get_flush();

            $this->socket->write(
                sprintf('%s', ClientPacketHeader::REQUEST_PLAYER_DATA->value)
            );

            while ($this->socket->isWritable() && $this->socket->isReadable()) {
                $rawPacketData = ServerPacketHeader::getPackets($this->socket->read());
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

        $needUpdate = false;
        switch ($serverPacketHeader) {
            case ServerPacketHeader::PLAYER_UPDATED:
                $this->player = unserialize($message);
                $needUpdate = true;
                break;
            case ServerPacketHeader::UI_CURRENT_TARGET_UPDATED:
                /** @var Entity $currentTarget */
                echo $message;
                $currentTarget = unserialize($message);
                $this->currentTargets[$currentTarget->getId()] = $currentTarget;

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

            /** @var InGameName $inGameName */
            $inGameName = $this->player->getComponent(InGameName::class);

            /** @var MapPosition $mapPosition */
            $mapPosition = $this->player->getComponent(MapPosition::class);

            echo sprintf(
                "%s (%d,%d) - HP: %d/%d - %s\n",
                $mapSymbol->getSymbol(),
                $mapPosition->get()->getX(),
                $mapPosition->get()->getY(),
                $hitPoints->getCurrent(),
                $hitPoints->getTotal(),
                $inGameName->getInGameName(),
            );

            $this->currentTargets = array_filter(
                $this->currentTargets,
                function (Entity $currentTarget) {
                    /** @var HitPoints $hitPoints */
                    $hitPoints = $currentTarget->getComponent(HitPoints::class);

                    return ($hitPoints?->getCurrent() ?? 0) > 0;
                },
            );

            if (count($this->currentTargets) > 0) {
                echo "Targets:\n";
                $index = 1;
                foreach ($this->currentTargets as $currentTarget) {
                    /** @var MapSymbol $mapSymbol */
                    $mapSymbol = $currentTarget->getComponent(MapSymbol::class);
                    /** @var HitPoints $hitPoints */
                    $hitPoints = $currentTarget->getComponent(HitPoints::class);
                    /** @var InGameName $inGameName */
                    $inGameName = $currentTarget->getComponent(InGameName::class);
                    /** @var MapPosition $mapPosition */
                    $mapPosition = $currentTarget->getComponent(MapPosition::class);


                    echo sprintf(
                        "\t%d - %s (%d,%d) - HP: %d/%d - %s\n",
                        $index++,
                        $mapSymbol->getSymbol(),
                        $mapPosition->get()->getX(),
                        $mapPosition->get()->getY(),
                        $hitPoints->getCurrent(),
                        $hitPoints->getTotal(),
                        $inGameName->getInGameName(),
                    );
                }
            }
        }

        ob_end_flush();
    }
}

