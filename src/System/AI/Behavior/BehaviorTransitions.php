<?php

declare(strict_types=1);

namespace App\System\AI\Behavior;

use App\Engine\Component\ComponentInterface;

class BehaviorTransitions
{
    /** @var string[] */
    private array $from = [];

    /** @var BehaviorTransition[] */
    private array $to = [];

    /**
     * @param string[] $from;
     */
    public function __construct(array $from, array $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @return string[]
     */
    public function getFrom(): array
    {
        return $this->from;
    }

    /** @return BehaviorTransition[] */
    public function getTo(): array
    {
        return $this->to;
    }



    public function canTransitionFrom(?BehaviorPreset $previousBehavior): bool
    {
        return empty($this->from) || in_array($previousBehavior->getName(), $this->from);
    }

    public function canTransitionTo(?BehaviorPreset $targetBehavior): bool
    {
        //todo implement
        $silent = $targetBehavior->isSilent();
        //todo any non silent behavior should be in the list to be triggered.


        return true;
    }


}
