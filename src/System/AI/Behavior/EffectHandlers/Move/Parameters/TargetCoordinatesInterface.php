<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers\Move\Parameters;

use App\System\AI\Behavior\EffectHandlers\EffectParameterInterface;

interface TargetCoordinatesInterface extends EffectParameterInterface
{
    public function getX(int $fromX): int;
    public function getY(int $fromY): int;
}
