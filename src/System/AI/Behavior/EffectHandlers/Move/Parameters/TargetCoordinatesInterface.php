<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers\Move\Parameters;

use App\Engine\Entity\Entity;
use App\System\AI\Behavior\EffectHandlers\EffectParameterInterface;
use App\System\Helpers\Point2D;

interface TargetCoordinatesInterface extends EffectParameterInterface
{
    public function getTargetPoint(Point2D $from, Entity $targetEntity): Point2D;
}
