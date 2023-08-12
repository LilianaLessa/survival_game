<?php

declare(strict_types=1);

namespace App\System;

use Amp\Socket;
use App\Engine\System\ReceiverSystemInterface;
use function Amp\async;

class TCPCommandReceiver
{
    public function __construct(private readonly string $address, private readonly array $systems)
    {
    }

    public function init(): void
    {
        $server = Socket\listen($this->address);
        echo 'TCP Command listener on ' . $server->getAddress() . ' ...' . PHP_EOL;
        async(function () use ($server) {
            while ($socket = $server->accept()) {
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
                    } while($data !== null && $data !=='exit');
                    //todo give an exit command.
                    $socket->close();
                });
            }
        });
    }
}
