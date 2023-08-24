<?php

declare(strict_types=1);

namespace App\System\Server\Client;

use App\Engine\System\WorldActorActionType;
use App\System\CommandPredicate;
use App\System\Direction;
use App\System\Key;
use App\System\PresetLibrary\PresetDataType;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ClientPacketHeader;
use App\System\Server\ServerPresetLibrary;
class UnblockingCliClient extends AbstractClient
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
                $this->unblockingInputMode();
            }
        }

        echo "\n\nConnection closed. GoodBye!\n\n";
    }

    protected function getSocketType(): SocketType
    {
        return SocketType::UNBLOCKING_CLI;
    }

    private function unblockingInputMode(): void
    {
        $stdin = fopen('php://stdin', 'r');
        stream_set_blocking($stdin, false);
        system('stty cbreak -echo');

        $keypress = fgets($stdin);
        if ($keypress) {
            $command = $this->key2Command($keypress);
            $this->socket->write(
                sprintf('%s %s', ClientPacketHeader::GAME_COMMAND->value, $command)
            );
        }
        fclose($stdin);
    }

    private function key2Command($string): string {
        return match (Key::tryFrom($string)) {
            Key::ARROW_UP =>
                sprintf("action %s %s", WorldActorActionType::PRIMARY->value, Direction::UP->value),
            Key::ARROW_DOWN =>
                sprintf("action %s %s", WorldActorActionType::PRIMARY->value, Direction::DOWN->value),
            Key::ARROW_RIGHT =>
                sprintf("action %s %s", WorldActorActionType::PRIMARY->value, Direction::RIGHT->value),
            Key::ARROW_LEFT =>
                sprintf("action %s %s", WorldActorActionType::PRIMARY->value, Direction::LEFT->value),
            Key::W => Direction::UP->value,
            Key::S => Direction::DOWN->value,
            Key::D => Direction::RIGHT->value,
            Key::A => Direction::LEFT->value,
            Key::I => CommandPredicate::INVENTORY->value,
            default => '',
        };
    }
}
