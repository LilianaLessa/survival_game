<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers\Move\Parameters;

enum TargetCoordinateTypes: string
{
    case RANDOM_COORDINATES = 'randomCoordinates';
    case RANDOM_BIOMA_COORDINATES = 'randomBiomaCoordinates';
    case LAST_KNOWN_SELF_BIOME_COORDINATES = 'lastKnownSelfBiomeCoordinates';

    public function getParameterClassName(): string
    {
        return match($this) {
            //self::RANDOM_COORDINATES => RandomTargetCoordinates::class,
            default => RandomTargetCoordinates::class,
        };
    }

}
