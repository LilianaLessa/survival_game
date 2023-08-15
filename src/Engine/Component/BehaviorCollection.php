<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\System\AI\Behavior\BehaviorPreset;

class BehaviorCollection implements ComponentInterface
{
    /** @var BehaviorPreset[] */
    private array $behaviors;

    public function __construct(BehaviorPreset ...$behaviors)
    {
        $this->behaviors = $behaviors;
    }

    /** @return BehaviorPreset[] */
    public function getBehaviors(): array
    {
        return $this->behaviors;
    }
}
