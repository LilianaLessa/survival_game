<?php

declare(strict_types=1);

namespace App\Engine\Component;

class MsTimeFromLastAttack implements ComponentInterface
{
    public function __construct(
        private readonly int $msTime
    )
    {
    }

    public function getMsTime(): int
    {
        return $this->msTime;
    }
}
