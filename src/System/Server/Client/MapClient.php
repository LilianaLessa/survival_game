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

class MapClient extends AbstractClient
{
    private ?EntityCollection $entityCollection = null;
    private ?Dimension2D $mapDimensions = null;
    private ?Entity $viewer = null;

    private string|false $clientIdString;
    private int $screenId;

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

            //todo request screen id and put it on the right place
            $this->screenId = 1;

            $this->requestPlayerData();

            $this->requestMapData();

            $this->clientScreenUpdater->startAsyncUpdate($this);

            while ($this->socket->isWritable() && $this->socket->isReadable()) {
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

    protected function getSocketType(): SocketType
    {
        return SocketType::MAP;
    }

    private function processNextMessage(ServerPacketHeader $serverPacketHeader, string ...$packetData): void
    {
        $message = implode(' ', $packetData);

        switch ($serverPacketHeader) {
            case ServerPacketHeader::PLAYER_UPDATED:
                $this->viewer = unserialize($message);
                $this->entityCollection = $this->entityCollection ?? new EntityCollection();
                $this->entityCollection[$this->viewer->getId()] = $this->viewer;
                break;
            case ServerPacketHeader::MAP_INFO_UPDATED:
                //dimensions
                //todo current chunks background colors.
                $this->mapDimensions = unserialize($message);
                break;
            case ServerPacketHeader::MAP_ENTITY_UPDATED:
                /** @var Entity $entity */
                $entity = unserialize($message);
                $this->entityCollection = $this->entityCollection ?? new EntityCollection();

                $this->entityCollection[$entity->getId()] = $entity;
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
        $this->socket->write(
            sprintf('%s', ClientPacketHeader::REQUEST_PLAYER_DATA->value)
        );

        $rawPackageData = $this->socket->read();
        [$packageHeader, $packageData] = $this->parsePackage($rawPackageData);
        if ($packageHeader) {
            $this->processNextMessage($packageHeader, ...$packageData);
        }
    }

    private function requestMapData(): array
    {
        $this->socket->write(
            sprintf('%s', ClientPacketHeader::REQUEST_MAP_DATA->value)
        );

        $rawPackageData = $this->socket->read();
        [$packageHeader, $packageData] = $this->parsePackage($rawPackageData);
        if ($packageHeader) {
            $this->processNextMessage($packageHeader, ...$packageData);
        }
        return array($rawPackageData, $packageHeader, $packageData);
    }
}

