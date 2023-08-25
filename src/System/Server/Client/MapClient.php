<?php

declare(strict_types=1);

namespace App\System\Server\Client;

use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityCollection;
use App\System\Helpers\Dimension2D;
use App\System\PresetLibrary\PresetDataType;
use App\System\Screen\ClientScreenUpdater;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ClientPacketHeader;
use App\System\Server\ServerPacketHeader;
use App\System\Server\ServerPresetLibrary;
use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;

class MapClient extends AbstractClient
{
    private ?EntityCollection $entityCollection = null;
    private ?Dimension2D $mapDimensions = null;
    private ?Entity $viewer = null;

    private string|false $clientIdString = '';
    private int $screenId;

    /** @var ConsoleColor[][] */
    private array $backgroundColorData = [];

    public function __construct(
        ServerPresetLibrary $serverPresetLibrary,
        private readonly ClientScreenUpdater $clientScreenUpdater,
    )
    {
        [ $mapServer ] = $serverPresetLibrary->getPresetByNameAndTypes(
            'main',
            PresetDataType::SERVER_CONFIG
        );

        parent::__construct($mapServer);
    }

    public function getEntityCollection(): ?EntityCollection
    {
        return $this->entityCollection;
    }

    public function getMapDimensions(): ?Dimension2D
    {
        return $this->mapDimensions;
    }

    public function getViewer(): ?Entity
    {
        return $this->viewer;
    }

    public function getClientIdString(): false|string
    {
        return $this->clientIdString;
    }

    public function getScreenId(): int
    {
        return $this->screenId;
    }

    public function getBackgroundColorData(): array
    {
        return $this->backgroundColorData;
    }

    public function start(): void
    {
        $this->socket->write(ClientPacketHeader::REQUEST_CLIENT_UUID->pack());
        //todo request screen id and put it on the right place
        $this->screenId = 1;

        $this->requestPlayerData();

        $this->requestMapData();

        $this->requestPlayerSurroundingEntities();

        $this->clientScreenUpdater->startAsyncUpdate($this);

        while ($this->socket->isWritable() && $this->socket->isReadable()) {
            $rawPacketData = ServerPacketHeader::getPackets($this->readSocket());
            foreach ($rawPacketData as $rawPacket) {
                [$packetHeader, $packet] = $this->parsePacket($rawPacket);
                if ($packetHeader) {
                    $this->processNextMessage($packetHeader, ...$packet);
                }
            }
        }

        echo "\n\nConnection closed. GoodBye!\n\n";
    }

    protected function getSocketType(): SocketType
    {
        return SocketType::MAP;
    }

    private function processNextMessage(ServerPacketHeader $serverPacketHeader, string ...$packetData): void
    {
        $message = implode(' ', $packetData);

        switch ($serverPacketHeader) {
            case ServerPacketHeader::CLIENT_ID:
                $this->clientIdString = $message;
                break;
            case ServerPacketHeader::PLAYER_UPDATED:
                $entity = unserialize($message);
                $entity && $this->viewer = $entity;
                break;
            case ServerPacketHeader::MAP_INFO_UPDATED:
                [$mapDimensions, $colorData] = unserialize($message);

                $this->mapDimensions = $mapDimensions;

                foreach ($colorData as $x => $rows) {
                    foreach ($rows as $y => $color) {
                        $this->backgroundColorData[$x][$y] = $color;
                    }
                }

                break;
            case ServerPacketHeader::MAP_ENTITY_UPDATED:
                /** @var Entity $entity */
                $entity = unserialize($message);

                $this->entityCollection = $this->entityCollection ?? new EntityCollection();

                $entity && $this->entityCollection[$entity->getId()] = $entity;

                break;
            case ServerPacketHeader::MAP_ENTITY_REMOVED:
                $entityId = $message;
                if ($this->entityCollection) {
                    unset($this->entityCollection[$entityId]);
                }
                break;
            default:
                break;
        }
    }

    private function requestPlayerData(): void
    {
        $this->socket->write(ClientPacketHeader::REQUEST_PLAYER_DATA->pack());
    }

    private function requestMapData(): void
    {
        $this->socket->write(ClientPacketHeader::REQUEST_MAP_DATA->pack());
    }

    private function requestPlayerSurroundingEntities()
    {
        $this->socket->write(ClientPacketHeader::REQUEST_PLAYER_SURROUNDING_ENTITIES->pack());
    }
}

