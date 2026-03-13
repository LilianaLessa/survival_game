<?php

namespace App\System\NPC;

enum InteractionType: string {
    case DIALOG_CHOICE = 'DIALOG_CHOICE';
    case END_INTERACTION = 'END_INTERACTION';

    case START_INTERACTION = 'START_INTERACTION';
}