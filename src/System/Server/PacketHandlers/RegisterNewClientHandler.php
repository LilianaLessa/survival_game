<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\Socket\ResourceSocket;
use App\System\Player\PlayerFactory;
use App\System\Player\PlayerPresetLibrary;
use App\System\Server\Client\Network\Client;
use App\System\Server\Client\Network\ClientPool;
use App\System\Server\Client\Network\Socket;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ServerPacketHeader;
use App\System\World\WorldManager;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\UuidInterface;
use function Amp\delay;

class RegisterNewClientHandler implements ClientPacketHandlerInterface
{
    public function __construct(
        private readonly ClientPool $clientPool,
        private readonly PlayerPresetLibrary $playerPresetLibrary,
        private readonly WorldManager $worldManager,
        private readonly PlayerFactory $playerFactory,
    ) {
    }

    public function handle(ResourceSocket $socket, UuidInterface $socketUuid, string ...$packetData): void
    {
        $newClient = $this->createNewClient($socketUuid, $socket);

        $this->createPlayer($newClient, $socketUuid->toString(), $packetData);

        $this->sendRegisterSuccess($socket, $newClient);
    }

    private function createNewClient(UuidInterface $socketUuid, ResourceSocket $socket): Client
    {
        $newClient = new Client(UuidV4::uuid4());

        $newClient->addSocket(
            new Socket(
                $socketUuid,
                $socket,
                SocketType::MAIN
            )
        );

        $this->clientPool->addClient($newClient);
        return $newClient;
    }

    private function createPlayer(Client $newClient, string $socketUuid, array $packetData): void
    {
        //todo load player data
        $playerPreset = $this->playerPresetLibrary->getDefaultPlayerPreset();

        //create player instance
        $newPlayer = $this->playerFactory->create($playerPreset, $socketUuid);

        $newClient->setPlayer($newPlayer);
    }

    private function sendRegisterSuccess(ResourceSocket $socket, Client $newClient): void
    {
        $socket->write(sprintf(
            "%s %s\n",
            ServerPacketHeader::CLIENT_REGISTER_SUCCESS->value,
            $newClient->getUuid()->toString()
        ));
    }
}
