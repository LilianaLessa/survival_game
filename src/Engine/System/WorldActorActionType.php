<?php

declare(strict_types=1);

namespace App\Engine\System;

enum WorldActorActionType: string
{
    case MOVE = 'move'; //todo this should be implemented.
    case PRIMARY = 'primary';
    case SECONDARY = 'secondary';
    case SHORTCUT_1 = '1';
    case SHORTCUT_2 = '2';
    case SHORTCUT_3 = '3';
}
