<?php

declare(strict_types=1);

namespace App\Engine\Component\ActionHandler;

use App\Engine\Component\ComponentInterface;
use App\Engine\System\WorldActorActionType;

class ActionHandlerList implements ComponentInterface
{
    /**
     * @parameter ActionHandlerInterface[] $actionHandlerList
     */
    public function __construct(private readonly array $actionHandlerList)
    {
    }

    public function getActionByType(?WorldActorActionType $type): ?ActionHandlerInterface
    {
        return $this->actionHandlerList[$type?->value ?? null] ?? null;
    }
}
