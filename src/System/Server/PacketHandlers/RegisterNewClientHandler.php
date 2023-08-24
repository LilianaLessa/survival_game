<?php

declare(strict_types=1);

namespace App\System\Server\PacketHandlers;

use Amp\Socket\ResourceSocket;
use App\System\Player\PlayerFactory;
use App\System\Player\PlayerPresetLibrary;
use App\System\Server\Client\Client;
use App\System\Server\Client\ClientPool;
use App\System\Server\Client\Socket;
use App\System\World\WorldManager;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\UuidInterface;

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

        $response = sprintf(
            "%s %s",
            'register_new_client_response',
            $newClient->getUuid()->toString()
        );

        $socket->write($response);
    }

    private function createNewClient(UuidInterface $socketUuid, ResourceSocket $socket): Client
    {
        $newClient = new Client(UuidV4::uuid4());

        $newClient->addSocket(
            new Socket(
                $socketUuid,
                $socket
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
}
