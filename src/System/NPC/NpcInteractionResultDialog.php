<?php

namespace App\System\NPC;

class NpcInteractionResultDialog extends AbstractNpcInteractionResult
{
    public function __construct(
        public readonly string $text,
        public readonly array $choices,
    )
    {
        parent::__construct(NpcInteractionResultType::DIALOG);
    }
}