<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Entity\Entity;

interface ReceiverSystemInterface
{
    /** @param Entity[] $entityCollection */
    public function parse(string $command): void;
}
