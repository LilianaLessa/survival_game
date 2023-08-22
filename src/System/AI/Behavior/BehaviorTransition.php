<?php

declare(strict_types=1);

namespace App\System\AI\Behavior;

class BehaviorTransition
{
    /** @var BehaviorTrigger[]  */
    private array $triggers;

    public function __construct(private readonly string $behaviorName, BehaviorTrigger ...$triggers)
    {
        $this->triggers = $triggers;
    }

    public function getTriggers(): array
    {
        return $this->triggers;
    }

    public function getBehaviorName(): string
    {
        return $this->behaviorName;
    }
}
