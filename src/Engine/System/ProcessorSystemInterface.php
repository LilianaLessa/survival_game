<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Entity\Entity;

interface ProcessorSystemInterface
{
    /** @param Entity[] $entityCollection */
    public function process(array $entityCollection): void;
}
