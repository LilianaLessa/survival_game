<?php

namespace App\System;

enum CommandPredicate : string {
    case EXIT = 'exit';

    //directions
    case PLAYER_MOVE_UP = 'w';
    case PLAYER_MOVE_DOWN = 's';
    case PLAYER_MOVE_LEFT = 'a';
    case PLAYER_MOVE_RIGHT = 'd';
    case MINE = 'm';
    case INVENTORY = 'i';
    case BUILD = 'b';
    case PLACE_OBJECT = 'p';

    case WORLD_SET_VIEW = 'view';
}
