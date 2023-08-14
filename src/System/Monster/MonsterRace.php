<?php

declare(strict_types=1);

namespace App\System\Monster;

enum MonsterRace: string {
    case BEAST = 'beast';
    case UNDEAD = 'undead';
    case HUMANOID = 'humanoid';
    // Add more races here
}
