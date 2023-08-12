<?php

declare(strict_types=1);

namespace App\System;

enum ForceDirection: string
{
    case W = "←";
    case E = "→";
    case N = "↑";
    case S = "↓";
    case NW = "↖";
    case NE = "↗";
    case SE = "↘";
    case SW = "↙";

    /** @return int[] */
    public function getVectorForce(): array
    {
        $f = match ($this) {
            self::W => [-1, 0],
            self::E => [1, 0],
            self::N => [0, -1],
            self::S => [0, 1],
            self::NW => [-1, -1],
            self::NE => [1, -1],
            self::SW => [-1, 1],
            self::SE => [1, 1],
        };

        return [
            'x' => $f[0],
            'y' => $f[1],
        ];
    }

    public static function random(): self
    {
        $valid = self::cases();

        return $valid[rand(0, count($valid)-1)];
    }
}
