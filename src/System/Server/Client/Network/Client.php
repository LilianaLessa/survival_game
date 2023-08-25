<?php

declare(strict_types=1);

namespace App\System\Server\Client\Network;

use App\Engine\Entity\Entity;
use Ramsey\Uuid\UuidInterface;

class Client
{
    /** @var Socket[] */
    private array $sockets;

    private ?Entity $player = null;

    public function __construct(private readonly UuidInterface $uuid)
    {
         $this->sockets = [];
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /** @return Socket[] */
    public function getSockets(): array
    {
        return $this->sockets;
    }

    /** @return Socket[] */
    public function getSocketsByType(SocketType ...$types): array
    {
        $sockets = [];

        foreach ($this->sockets as $socket) {
            in_array($socket->getSocketType(), $types) && $sockets[] = $socket;
        }

        return $sockets;
    }

    public function getSocketByUuid(string $uuid): ?Socket
    {
        return $this->sockets[$uuid] ?? null;
    }

    public function removeSocket(Socket $socket): void
    {
        unset($this->sockets[$socket->getUuid()->toString()]);
    }

    public function addSocket(Socket $socket): void
    {
        $this->sockets[$socket->getUuid()->toString()] = $socket;
    }

    public function shutDown()
    {
        //do clean up related to client, as such as removing the player and related controllers.

    }

    public function getPlayer(): ?Entity
    {
        return $this->player;
    }

    public function setPlayer(Entity $player): self
    {
        $this->player = $player;
        return $this;
    }

    public function send(string $data, SocketType ...$socketTypes): void
    {
        $sockets = $this->getSocketsByType(...$socketTypes);
        foreach ($sockets as $socket) {
            $socket->getSocket()->write($data);
        }
    }
}
