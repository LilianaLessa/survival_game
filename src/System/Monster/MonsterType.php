<?php

declare(strict_types=1);

namespace App\System\Monster;

enum MonsterType: string {
    case ANIMAL = 'animal';
    case UNDEAD = 'undead';
    case SPIRIT = 'spirit';
    // Add more types here
}
