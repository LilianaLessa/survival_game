<?php

namespace App\System;

enum CommandPredicate : string {
    case EXIT = 'exit';

    //directions
    case PLAYER_MOVE_UP = 'w';
    case PLAYER_MOVE_DOWN = 's';
    case PLAYER_MOVE_LEFT = 'a';
    case PLAYER_MOVE_RIGHT = 'd';
    case PLAYER_WARP = 'warp';

    case PLAYER_SELF_WHERE = 'where';
    case PLAYER_VIEWPORT = 'vi';

    case PLAYER_ACTION = 'action';

    case PLAYER_SET_NAME = 'set_name';
    case PLAYER_SET_COLOR = 'set_color';

    case MINE = 'm';
    case INVENTORY = 'i';

    case DEBUG_INSPECT_CELL = 'ins';
    case DEBUG_INSPECT_ENTITY = 'inse';
    case DEBUG_GIVE_ITEM = 'item_give';

    case BUILD = 'b';
    case PLACE_OBJECT = 'p';

    case WORLD_SET_VIEW = 'view';

}
