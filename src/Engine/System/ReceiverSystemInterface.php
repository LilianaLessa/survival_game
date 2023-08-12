<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Entity\Entity;

interface ReceiverSystemInterface
{
    public function parse(string $rawCommand): void;
}
