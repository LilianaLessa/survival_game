<?php

declare(strict_types=1);

namespace App\Engine\Commands;

interface InvokableCommandInterface extends CommandInterface
{
    public function __invoke();
}
