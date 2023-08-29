<?php

declare(strict_types=1);

use App\Game;

require_once('vendor/autoload.php');

$game = new Game();

$seed = (float)($argv[1] ?? 1);
$game->init($seed);
$game->start();
