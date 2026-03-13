<?php

namespace App\System\NPC;
class AbstractNpcInteractionResult
{
    public function __construct(
        public readonly NpcInteractionResultType $type
    )
    {
    }
}