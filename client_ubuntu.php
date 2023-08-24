<?php

require_once 'vendor/autoload.php';

use App\System\Kernel;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ServerPresetLibrary;

/** @var ServerPresetLibrary $serverLibrary */
$serverLibrary = Kernel::getContainer()->get(ServerPresetLibrary::class);
$serverLibrary->load('./data/Server');

$socketType = SocketType::tryfrom($argv[2] ?? '') ?? SocketType::MAIN;

$client = $socketType->getClient();

if (!$client) {
    echo 'invalid client type';
    die;
}

$client->init($argv[1] ?? null) === false && die;

$client->start();
