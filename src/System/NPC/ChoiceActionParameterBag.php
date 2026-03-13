<?php

namespace App\System\NPC;

readonly class ChoiceActionParameterBag
{
    /** @var ChoiceActionParameter[] */
    public array $parameters;

    public function __construct(
        ChoiceActionParameter ...$parameters
    )
    {
        $this->parameters = $parameters;
    }

    public function getParameterByName(string $name): ?ChoiceActionParameter
    {
        $result = null;

        foreach ($this->parameters as $parameter) {
            if ($parameter->name === $name) {
                $result = $parameter;
                break;
            }
        }

        return $result;
    }
}