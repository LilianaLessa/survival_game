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

    public function getPrimaryInverse(): self
    {
        return match($this) {
            self::W => self::E,
            self::E => self::W,
            self::N => self::S,
            self::S => self::N,
            self::NW => self::SE,
            self::NE => self::SW,
            self::SW => self::NE,
            self::SE => self::NW,
        };
    }

    /**
     * return 45 degree direction changes
     *
     * @return self[]
     */
    public function getSecondaryInverses(): array
    {
        return match($this) {
            self::W => [self::NE,self::SE],
            self::E => [self::NW,self::SW],
            self::N => [self::SE, self::SW],
            self::S => [self::NE, self::NW],
            self::NW => [self::S, self::E],
            self::NE => [self::S, self::W],
            self::SW => [self::N, self::E],
            self::SE => [self::N, self::W],
        };
    }

    /**
     * return 90 degree direction changes
     *
     * @return self[]
     */
    public function getTertiaryInverses(): array
    {
        return match($this) {
            self::W,
            self::E => [self::N , self::S],
            self::N,
            self::S => [self::E , self::W],
            self::NW => [self::NE, self::SW],
            self::NE => [self::NW, self::SE],
            self::SW => [self::SE, self::NW],
            self::SE => [self::SW, self::NE],
        };
    }
}
