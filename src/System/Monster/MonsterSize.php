<?php

declare(strict_types=1);

namespace App\System\Monster;

enum MonsterSize: string {
    case TINY = 'tiny';
    case SMALL = 'small';
    case MEDIUM = 'medium';
    case LARGE = 'large';
    // Add more sizes here
}
