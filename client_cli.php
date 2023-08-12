<?php

require_once 'vendor/autoload.php';

function translateKeypress($string) {
    switch ($string) {
        case "\033[A":
            return "UP";
        case "\033[B":
            return "DOWN";
        case "\033[C":
            return "RIGHT";
        case "\033[D":
            return "LEFT";
        case "\n":
            return "ENTER";
        case " ":
            return "SPACE";
        case "\010":
        case "\177":
            return "BACKSPACE";
        case "\t":
            return "TAB";
        case "\e":
            return "ESC";
    }
    return $string;
}

use Amp\Socket\ClientTlsContext;
use Amp\Socket\ConnectContext;
use League\Uri\Http;
use function Amp\Socket\connect;
use function Amp\Socket\connectTls;
use function Amp\delay;

function connectToGameServer(string $address): \Amp\Socket\Socket
{
    $uri = Http::createFromString($address);
    $host = $uri->getHost();
    $port = $uri->getPort() ?? ($uri->getScheme() === 'https' ? 443 : 80);

    $connectContext = (new ConnectContext)
        ->withTlsContext(new ClientTlsContext($host));

    return $uri->getScheme() === 'http'
        ? connect($host . ':' . $port, $connectContext)
        : connectTls($host . ':' . $port, $connectContext);
}

function unblockingInputMode(\Amp\Socket\Socket $socket): void
{
    $stdin = fopen('php://stdin', 'r');
    stream_set_blocking($stdin, 0);
    system('stty cbreak -echo');

    $keypress = fgets($stdin);
    if ($keypress) {
        $key = translateKeypress($keypress);
        $socket->write($key);
    }

    fclose($stdin);
}

function commandInputMode(\Amp\Socket\Socket $socket): void
{
    $command = readline('>>');
    $socket->write($command);
}

function messageReceiverMode(\Amp\Socket\Socket $socket): void
{
    $data = $socket->read();
    if ($data) {
       echo $data;
    }
}

echo match($argv[2] ?? null) {
    '-u' => "\n\nUnblocking command input mode active\n\n",
    '-m' => "\n\nMessage Receiver mode active\n\n",
    '-w' => "\n\nMap viewer mode active\n\n",
    '-i' => "\n\nPersistent interface mode active\n\n",
    '-c' => "\n\nCommand input mode active\n\n",
    default => "\n\nCommand input mode active\n\n",
};



$socket = null;
do {
    if (!$socket || $socket->isClosed()) {
        echo "No connection found. Trying to connect...";
        while(!$socket || $socket->isClosed()) {
            try {
                echo ".";
                $socket = connectToGameServer($argv[1]);
                echo "Connected!\n\n";
                break;
            } catch (\Throwable $e) {}
        }
    }

    try {
        if ($socket->isWritable() && $socket->isReadable()) {
            switch ($argv[2] ?? null) {
                case '-u':
                    unblockingInputMode($socket);
                    break;
                case '-m':
                    //subscribe to messages
                    messageReceiverMode($socket);
                    break;
                case '-w':
                    //subscribe to map
                    //messageReceiverMode($socket);
                    break;
                case '-i':
                    //this is an updatable interface view, that should show info like
                    // --- tool equipped
                    // --- shortcut list
                    // hp/sp?
                    // statuses
                    //messageReceiverMode($socket);
                    break;
                case '-c':
                default:
                    commandInputMode($socket);
                    break;
            }
            continue;
        }
    } catch (\Throwable) {}

    $socket->close();
    $socket = null;
    echo "\n\nConnection Closed!\n\n";
} while (1);

