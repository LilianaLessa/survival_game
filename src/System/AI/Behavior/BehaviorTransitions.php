<?php

declare(strict_types=1);

namespace App\System\AI\Behavior;

use App\Engine\Component\ComponentInterface;

class BehaviorTransitions
{
    /** @var string[] */
    private array $from;

    /**
     * @param string[] $from;
     */
    public function __construct(array $from)
    {
        $this->from = $from;
    }

    /**
     * @return string[]
     */
    public function getFrom(): array
    {
        return $this->from;
    }

    public function canTransitionFrom(?BehaviorPreset $getBehaviorPreset): bool
    {
        return empty($this->from) || in_array($getBehaviorPreset->getName(), $this->from);
    }

    public function canTransitionTo(?BehaviorPreset $getBehaviorPreset): bool
    {
        //todo implement
        return true;
    }
}
