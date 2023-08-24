<?php

declare(strict_types=1);

namespace App\System\TCP;

use Amp\Socket;
use Amp\Socket\ResourceSocket;
use App\Engine\System\ReceiverSystemInterface;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\Server\ClientPacketHeader;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\UuidInterface;
use function Amp\async;

class TCPServer
{
    /** @var ResourceSocket[]  */
    private array $sockets;

    public function __construct(private readonly string $address, private readonly array $systems)
    {
    }

    public function init(): void
    {
        $this->sockets = [];
        $this->setUpUiMessageEventListener();

        $server = Socket\listen($this->address);
        echo 'TCP Command listener on ' . $server->getAddress() . ' ...' . PHP_EOL;
        $this->handleConnections($server);
    }

    private function setUpUiMessageEventListener(): void
    {
        Dispatcher::getInstance()->addListener(
            UiMessageEvent::EVENT_NAME,
            function (UiMessageEvent $event) {
                //todo send message only for the subscribers.
                foreach ($this->sockets as $socket) {
                    $socket->write($event->getMessage());
                }
            }
        );
    }

    private function handleConnections(Socket\ResourceServerSocket $server): void
    {
        async(function () use ($server) {
            while ($socket = $server->accept()) {
                $uuid = UuidV4::uuid4();
                $this->sockets[$uuid->toString()] = $socket;
                $this->handleMessages($socket, $uuid);
            }
        });
    }

    private function handleMessages(ResourceSocket $socket, UuidInterface $socketUuid): void
    {
        async(function () use ($socket, $socketUuid) {
            do {
                $data = null;
                if ($socket->isWritable() && $socket->isReadable()) {
                    $data = $socket->read();
                }

                $packageExplodedData = explode(' ', $data ?? '');

                $clientPackage = ClientPacketHeader::tryFrom($packageExplodedData[0] ?? '');

                if ($clientPackage) {
                    array_shift($packageExplodedData);
                    $clientPackage->getHandler()->handle($socket, $socketUuid, ...$packageExplodedData);
                }
            } while ($data !== null && $data !== 'exit');
            $socket->close();
            unset($this->sockets[$socketUuid->toString()]);
        });
    }
}
