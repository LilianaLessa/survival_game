<?php

namespace App\System\NPC;

readonly class DialogNodeChoice
{
    /** @param DialogNodeChoiceAction[] $actions */
    public function __construct(
        public string     $id,
        public string     $text,
        public DialogNode $nextNode,
        public array $actions = [],
        public array $conditions = []
    )
    {
    }
}