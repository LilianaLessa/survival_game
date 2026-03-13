<?php

namespace App\System\NPC;

readonly class ChoiceConditionParameter
{
    public function __construct(
        public string $name,
        public mixed  $value,
    )
    {
    }
}