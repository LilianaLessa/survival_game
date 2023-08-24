<?php

require_once 'vendor/autoload.php';

use Amp\Socket\ClientTlsContext;
use Amp\Socket\ConnectContext;
use App\System\Kernel;
use App\System\PresetLibrary\PresetDataType;
use App\System\Server\ServerPreset;
use App\System\Server\ServerPresetLibrary;
use League\Uri\Http;
use function Amp\Socket\connect;
use function Amp\Socket\connectTls;

/** @var ServerPresetLibrary $serverLibrary */
$serverLibrary = Kernel::getContainer()->get(ServerPresetLibrary::class);
$serverLibrary->load('./data/Server');

/** @var ServerPreset $mapServer */
[ $mapServer ] = $serverLibrary->getPresetByNameAndTypes('main', PresetDataType::SERVER_CONFIG);

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

$socket = connectToGameServer(
    sprintf(
        'http://%s:%s',
        $mapServer->getHost(),
        $mapServer->getPort(),
    )
);

$clientUuid = $argv[1] ?? null;

if ($clientUuid) {
    $socket->write(sprintf('register_client %s', $clientUuid));
} else {
    $socket->write(sprintf('register_new_client', $clientUuid));
}

while (1) {
    $data = $socket->read();
    if ($data) {
        echo $data;
    }
}

//$socket->write($command);
//$data = $socket->read();


