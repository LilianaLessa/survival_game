<?php

declare(strict_types=1);

namespace App\System\AI;

use App\Engine\Entity\Entity;

class TriggerValueEvaluatorWrapper
{
    public function __construct(private readonly \Closure $evaluator) {

    }

    public function evaluateTrigger(Entity $e): bool
    {
        return ($this->evaluator)($e);
    }
}
