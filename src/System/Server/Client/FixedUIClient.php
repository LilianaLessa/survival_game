<?php

declare(strict_types=1);

namespace App\System\Server\Client;

use App\Engine\Component\DefaultColor;
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
use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;

class FixedUIClient extends AbstractClient
{
    private ?Entity $player = null;

    /** @var Entity[] */
    private array $currentTargets = [];

    /** @var Entity[] */
    private $nearbyPlayers = [];

    private string|false $clientIdString;


    public function __construct(
        ServerPresetLibrary $serverPresetLibrary,
        private readonly ConsoleColor $consoleColor,
    ) {
        [ $mapServer ] = $serverPresetLibrary->getPresetByNameAndTypes(
            'main',
            PresetDataType::SERVER_CONFIG
        );

        parent::__construct($mapServer);
    }

    public function start(): void
    {
        $this->socket->write(ClientPacketHeader::REQUEST_CLIENT_UUID->pack());

        $rawPackageData = $this->readSocket();
        if ($rawPackageData) {
            ob_start();
            $this->printPacketInfo(
                ...$this->parsePacket($rawPackageData)
            );

            $this->clientIdString = ob_get_flush();

            $this->socket->write(ClientPacketHeader::REQUEST_PLAYER_DATA->pack());

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

        $needUpdate = false;
        switch ($serverPacketHeader) {
            case ServerPacketHeader::PLAYER_UPDATED:
                $this->player = unserialize($message);
                $needUpdate = true;
                break;
            case ServerPacketHeader::UI_CURRENT_TARGET_UPDATED:
                /** @var Entity $nearbyPlayer */
                $nearbyPlayer = unserialize($message);
                $this->currentTargets[$nearbyPlayer->getId()] = $nearbyPlayer;

                $needUpdate = true;
                break;
            case ServerPacketHeader::UI_NEARBY_PLAYER_EXISTS:
                /** @var Entity $currentTarget */
                $nearbyPlayer = unserialize($message);
                $this->nearbyPlayers[$nearbyPlayer->getId()] = $nearbyPlayer;

                $needUpdate = true;
                break;
            case ServerPacketHeader::UI_NEARBY_PLAYER_REMOVED:
                /** @var Entity $currentTarget */
                $nearbyPlayerId = $message;
                unset($this->nearbyPlayers[$nearbyPlayerId]);

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
            $this->renderPlayerInfo();

            $this->renderCurrentTargets();

            $this->renderNearbyPlayers();
        }

        ob_end_flush();
    }

    private function renderNearbyPlayers(): void
    {
        if (count($this->nearbyPlayers) > 0) {
            echo "\nNearby Players:\n";
            $index = 1;
            foreach ($this->nearbyPlayers as $nearbyPlayer) {

                /** @var MapSymbol $mapSymbol */
                /** @var HitPoints $hitPoints */
                /** @var InGameName $inGameName */
                /** @var MapPosition $mapPosition */
                /** @var DefaultColor $defaultColor */
                [
                    $mapSymbol,
                    $hitPoints,
                    $inGameName,
                    $mapPosition,
                    $defaultColor,
                ] = $nearbyPlayer->explode(
                    MapSymbol::class,
                    HitPoints::class,
                    InGameName::class,
                    MapPosition::class,
                    DefaultColor::class,
                );

                $nearbyPlayerSymbol = $this->consoleColor->apply(
                    sprintf('color_%d', $defaultColor->getColor()->toInt()),
                    $mapSymbol->getSymbol()
                );

                echo sprintf(
                    "\t%d - %s (%d,%d) - HP: %d/%d - %s\n",
                    $index++,
                    $nearbyPlayerSymbol,
                    $mapPosition->get()->getX(),
                    $mapPosition->get()->getY(),
                    $hitPoints->getCurrent(),
                    $hitPoints->getTotal(),
                    $inGameName->getInGameName(),
                );
            }
        }
    }

    private function renderCurrentTargets(): void
    {
        $this->currentTargets = array_filter(
            $this->currentTargets,
            function (Entity $currentTarget) {
                /** @var HitPoints $hitPoints */
                $hitPoints = $currentTarget->getComponent(HitPoints::class);

                return ($hitPoints?->getCurrent() ?? 0) > 0;
            },
        );

        if (count($this->currentTargets) > 0) {
            echo "\nTargets:\n";
            $index = 1;
            foreach ($this->currentTargets as $currentTarget) {

                /** @var MapSymbol $mapSymbol */
                /** @var HitPoints $hitPoints */
                /** @var InGameName $inGameName */
                /** @var MapPosition $mapPosition */
                /** @var DefaultColor $defaultColor */
                [
                    $mapSymbol,
                    $hitPoints,
                    $inGameName,
                    $mapPosition,
                    $defaultColor,
                ] = $currentTarget->explode(
                    MapSymbol::class,
                    HitPoints::class,
                    InGameName::class,
                    MapPosition::class,
                    DefaultColor::class,
                );

                $currentTargetSymbol = $this->consoleColor->apply(
                    sprintf('color_%d', $defaultColor->getColor()->toInt()),
                    $mapSymbol->getSymbol()
                );

                echo sprintf(
                    "\t%d - %s (%d,%d) - HP: %d/%d - %s\n",
                    $index++,
                    $currentTargetSymbol,
                    $mapPosition->get()->getX(),
                    $mapPosition->get()->getY(),
                    $hitPoints->getCurrent(),
                    $hitPoints->getTotal(),
                    $inGameName->getInGameName(),
                );
            }
        }
    }

    private function renderPlayerInfo(): void
    {
        /** @var MapSymbol $mapSymbol */
        /** @var HitPoints $hitPoints */
        /** @var InGameName $inGameName */
        /** @var MapPosition $mapPosition */
        /** @var DefaultColor $defaultColor */
        [
            $mapSymbol,
            $hitPoints,
            $inGameName,
            $mapPosition,
            $defaultColor,
        ] = $this->player->explode(
            MapSymbol::class,
            HitPoints::class,
            InGameName::class,
            MapPosition::class,
            DefaultColor::class,
        );

        $playerSymbol = $this->consoleColor->apply(
            sprintf('color_%d', $defaultColor->getColor()->toInt()),
            $mapSymbol->getSymbol()
        );

        echo sprintf(
            "%s (%d,%d) - HP: %d/%d - %s\n",
            $playerSymbol,
            $mapPosition->get()->getX(),
            $mapPosition->get()->getY(),
            $hitPoints->getCurrent(),
            $hitPoints->getTotal(),
            $inGameName->getInGameName(),
        );
    }
}

