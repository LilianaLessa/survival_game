<?php

declare(strict_types=1);

namespace App\System\Item;

use App\System\ConsoleColor;

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
            self::TRASH => ConsoleColor::Color_Off,
            self::COMMON => ConsoleColor::Green,
            self::UNCOMMON => ConsoleColor::Blue,
            self::RARE => ConsoleColor::BYellow,
            self::EPIC => ConsoleColor::BPurple,
            self::MYTHICAL => ConsoleColor::BIRed,
            self::LEGENDARY => ConsoleColor::BIGreen,
            self::DIVINE => ConsoleColor::BICyan,
        })->value;
    }
}
