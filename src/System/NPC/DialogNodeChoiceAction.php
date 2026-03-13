<?php

namespace App\System\NPC;

class DialogNodeChoiceAction
{
    public function __construct(
        public readonly DialogNodeChoiceActionType $actionType,
        public readonly ChoiceActionParameterBag $parameterBag,
    )
    {
    }
}