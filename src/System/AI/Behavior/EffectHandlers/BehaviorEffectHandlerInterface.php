<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers;

use App\System\AI\Behavior\BehaviorEffectConfig;
use App\System\AI\Behavior\BehaviorEffectType;

interface BehaviorEffectHandlerInterface
{
    public function shouldHandle(BehaviorEffectType $effectType): bool;
    public function handle(EffectParameterInterface ...$effectParameters): void;

    public static function buildEffectConfig(object $rawConfigData): BehaviorEffectConfig;
}
