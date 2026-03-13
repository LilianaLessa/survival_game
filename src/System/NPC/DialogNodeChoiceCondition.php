<?php

namespace App\System\NPC;

readonly class DialogNodeChoiceCondition
{
    public function __construct(
        public DialogNodeChoiceConditionType $conditionType,
        public ChoiceConditionParameterBag   $parameterBag,
    )
    {
    }
}