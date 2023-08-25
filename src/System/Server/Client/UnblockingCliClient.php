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
    private bool $unblockingActive = true;

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
                $command = match ($this->unblockingActive) {
                    true => $this->unblockingInputMode(),
                    false => $this->blockingInputMode(),
                };

                if ($command) {
                    if ($command === "\n") {
                        $this->unblockingActive = false;
                        continue;
                    }

                    $this->socket->write(ClientPacketHeader::GAME_COMMAND->pack($command));
                }

                $this->unblockingActive = true;
            }
        }

        echo "\n\nConnection closed. GoodBye!\n\n";
    }

    protected function getSocketType(): SocketType
    {
        return SocketType::UNBLOCKING_CLI;
    }

    private function unblockingInputMode(): ?string
    {
        $stdin = fopen('php://stdin', 'r');
        stream_set_blocking($stdin, false);
        system('stty cbreak -echo');

        $command = null;
        $keypress = fgets($stdin);
        if ($keypress) {
            $command = $this->key2Command($keypress);
        }
        fclose($stdin);
        return $command;
    }

    private function blockingInputMode(): ?string
    {
        system('stty cbreak echo');
        $command = readline("\n>>");
        system('stty cbreak -echo');

        return $command;
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
            Key::ENTER => "\n",
            default => '',
        };
    }
}
