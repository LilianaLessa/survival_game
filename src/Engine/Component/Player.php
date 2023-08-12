<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Entity\Entity;

class Player implements ComponentInterface
{
    static public function createPlayer($id, $x, $y):Entity
    {
        return new Entity(
            $id,
            new Player(),
            new MapPosition($x,$y),
            new MapSymbol("\033[1;33m☺\033[0m"),
            new Colideable(),
        );
    }
}
