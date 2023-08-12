<?php

declare(strict_types=1);

namespace App\System;

use Amp\Socket;
use Amp\Socket\ResourceSocket;
use App\Engine\System\ReceiverSystemInterface;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
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
                $this->sockets[] = $socket;
                $this->handleMessages($socket);
            }
        });
    }

    private function handleMessages(ResourceSocket $socket): void
    {
        async(function () use ($socket) {
            do {
                $data = $socket->read();
                if ($data) {
                    foreach ($this->systems as $system) {
                        if ($system instanceof ReceiverSystemInterface) {
                            $system->parse($data);
                        }
                    }
                }
            } while ($data !== null && $data !== 'exit');
            $socket->close();
        });
    }
}
