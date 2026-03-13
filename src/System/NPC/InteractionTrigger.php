<?php

namespace App\System\NPC;

class InteractionTrigger
{
    public function __construct(
        private readonly InteractionTriggerType $type,
        private readonly string $interactionHandlerId,
    )
    {
    }

    public function getType(): InteractionTriggerType
    {
        return $this->type;
    }

    public function getInteractionHandlerId(): string
    {
        return $this->interactionHandlerId;
    }
}
