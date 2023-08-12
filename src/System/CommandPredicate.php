<?php

namespace App\System;

enum CommandPredicate : string {
    case EXIT = 'exit';

    //directions
    case PLAYER_MOVE_UP = 'w';
    case PLAYER_MOVE_DOWN = 's';
    case PLAYER_MOVE_LEFT = 'a';
    case PLAYER_MOVE_RIGHT = 'd';

    case PLAYER_SELF_WHERE = 'where';

    case MINE = 'm';
    case INVENTORY = 'i';

    case DEBUG_INSPECT_CELL = 'ins';

    case BUILD = 'b';
    case PLACE_OBJECT = 'p';

    case WORLD_SET_VIEW = 'view';
}
