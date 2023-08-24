<?php

declare(strict_types=1);

namespace App\System;

//@see https://www.gnu.org/software/screen/manual/html_node/Input-Translation.html
//@see https://www.hashbangcode.com/article/creating-game-php-part-1-detecting-key-input
enum Key: string
{
    case ARROW_UP = "\033[A";
    case ARROW_DOWN = "\033[B";
    case ARROW_RIGHT = "\033[C";
    case ARROW_LEFT = "\033[D";
    case W = "w";
    case S = "s";
    case D = "d";
    case A  = "a";
    case I  = "i";

    case ENTER = "\n";
}
