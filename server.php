<?php

declare(strict_types=1);

use App\Game;

require_once('vendor/autoload.php');

$game = new Game();

$game->init();
//$game->initServerScreenUpdate();
$game->start();
