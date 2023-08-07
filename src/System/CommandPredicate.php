<?php

namespace App\System;

enum CommandPredicate : string {
    case EXIT = 'exit';

    //directions
    case UP = 'w';
    case DOWN = 's';
    case LEFT = 'a';
    case RIGHT = 'd';
    case MINE = 'm';
    case INVENTORY = 'i';
    case BUILD = 'b';
    case PLACE_OBJECT = 'p';
}
