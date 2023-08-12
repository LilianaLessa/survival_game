<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Entity\Entity;

class Monster implements ComponentInterface
{
    static public function createMonster($id, $x, $y): Entity
    {
        return new Entity(
            $id,
            new Monster(),
            new MapPosition($x,$y),
            new MapSymbol("\033[31m♞\033[0m"),
            new Colideable(),
        );
    }
}
