<?php

declare(strict_types=1);

namespace App\Engine\Component;

use App\Engine\Entity\Entity;

class Tree implements ComponentInterface
{
    static public function createTree($id, $x, $y): Entity
    {
        return new Entity(
            $id,
            new Tree(),
            new MapPosition($x,$y),
            new MapSymbol("\033[32m♣\033[0m"),
            new Colideable(),
        );
    }
}
