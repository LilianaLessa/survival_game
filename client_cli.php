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
use App\Engine\System\ReceiverSystemInterface;
use League\Uri\Http;
use function Amp\Socket\connect;
use function Amp\Socket\connectTls;

$uri = Http::createFromString($argv[1]);
$host = $uri->getHost();
$port = $uri->getPort() ?? ($uri->getScheme() === 'https' ? 443 : 80);
$path = $uri->getPath() ?: '/';

$connectContext = (new ConnectContext)
    ->withTlsContext(new ClientTlsContext($host));

$socket = $uri->getScheme() === 'http'
    ? connect($host . ':' . $port, $connectContext)
    : connectTls($host . ':' . $port, $connectContext);

function unblockingInputMode(\Amp\Socket\Socket $socket): void
{
    $stdin = fopen('php://stdin', 'r');
    stream_set_blocking($stdin, 0);
    system('stty cbreak -echo');

    do {
        $keypress = fgets($stdin);
        $key = null;
        if ($keypress) {
            $key = translateKeypress($keypress);
            $socket->write($key);
        }
    } while ($key !== 'ESC');
}

function commandInputMode(\Amp\Socket\Socket $socket): void
{
    do {
        $command = readline('>>');
        $socket->write($command);
    } while ($command !== 'exit');
}

function messageReceiverMode(\Amp\Socket\Socket $socket): void
{
    do {
        $data = $socket->read();
        if ($data) {
           echo $data;
        }
    } while (1);
}

switch ($argv[2] ?? null) {
    case '-u':
        echo "\n\nUnblocking input mode active\n\n";
        unblockingInputMode($socket);
        break;
    case '-m':
        echo "\n\nMessage Receiver mode active\n\n";
        //subscribe to messages
        messageReceiverMode($socket);
        break;
    case '-w':
        echo "\n\nMap viewer mode active\n\n";
        //subscribe to map
        //messageReceiverMode($socket);
        break;
    case '-i':
        echo "\n\nPersistent interface mode active\n\n";
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
