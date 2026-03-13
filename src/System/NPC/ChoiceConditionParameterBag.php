<?php

namespace App\System\NPC;

readonly class ChoiceConditionParameterBag
{
    /** @var ChoiceConditionParameter[] */
    public array $parameters;

    public function __construct(
        ChoiceConditionParameter ...$parameters
    )
    {
        $this->parameters = $parameters;
    }
}