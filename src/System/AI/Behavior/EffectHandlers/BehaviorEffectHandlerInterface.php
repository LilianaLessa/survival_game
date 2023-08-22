<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers;

use App\Engine\Entity\Entity;
use App\System\AI\Behavior\BehaviorEffectConfig;
use App\System\AI\Behavior\BehaviorEffectParameterConfig;
use App\System\AI\Behavior\BehaviorEffectType;
use App\System\World\WorldManager;

interface BehaviorEffectHandlerInterface
{
    public function handle(Entity $targetEntity, EffectParameterInterface ...$effectParameters): void;

    public static function shouldHandle(BehaviorEffectType $effectType): bool;
    public static function buildEffectConfig(object $rawConfigData): BehaviorEffectConfig;

    /** @return EffectParameterInterface[] */
    public static function buildEffectParameters(
        WorldManager $worldManager,
        BehaviorEffectParameterConfig ...$effectParameterConfigs
    ): array;
}
