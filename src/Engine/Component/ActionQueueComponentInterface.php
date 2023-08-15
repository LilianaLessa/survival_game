<?php

declare(strict_types=1);

namespace App\Engine\Component;


//todo consider using \SplQueue on implementations.
interface ActionQueueComponentInterface extends ComponentInterface
{
    public function isQueueEmpty(): bool;
}
