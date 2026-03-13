<?php

namespace App\System\NPC;

readonly class ChoiceActionParameter
{
    public function __construct(
        public string $name,
        public mixed  $value,
    )
    {
    }
}