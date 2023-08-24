<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\Engine\Component\PlayerCommandQueue;

interface InvokableCommandInterface extends CommandInterface
{
    public function __invoke(PlayerCommandQueue $playerCommandQueue);
}
