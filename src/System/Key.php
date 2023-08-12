<?php

declare(strict_types=1);

namespace App\System;

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
}
