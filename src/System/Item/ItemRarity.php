<?php

declare(strict_types=1);

namespace App\System\Item;

use App\System\ConsoleColorCode;

enum ItemRarity: string
{
    case TRASH = 'trash';
    case COMMON = 'common';
    case UNCOMMON = 'uncommon';
    case RARE = 'rare';
    case EPIC = 'epic';
    case MYTHICAL = 'mythical';
    case LEGENDARY = 'legendary';
    case DIVINE = 'divine';

    public function getColorCode(): string {
        return (match ($this) {
            self::TRASH => ConsoleColorCode::Color_Off,
            self::COMMON => ConsoleColorCode::Green,
            self::UNCOMMON => ConsoleColorCode::Blue,
            self::RARE => ConsoleColorCode::BYellow,
            self::EPIC => ConsoleColorCode::BPurple,
            self::MYTHICAL => ConsoleColorCode::BIRed,
            self::LEGENDARY => ConsoleColorCode::BIGreen,
            self::DIVINE => ConsoleColorCode::BICyan,
        })->value;
    }
}
