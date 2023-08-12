<?php

declare(strict_types=1);

namespace App\System;

enum ConsoleColor: string
{
    case Color_Off = "\033[0m"; // Text Reset

    // Regular Colors
    case Black = "\033[0;30m"; // Black
    case Red = "\033[0;31m"; // Red
    case Green = "\033[0;32m"; // Green
    case Yellow = "\033[0;33m"; // Yellow
    case Blue = "\033[0;34m"; // Blue
    case Purple = "\033[0;35m"; // Purple
    case Cyan = "\033[0;36m"; // Cyan
    case White = "\033[0;37m"; // White

    // Bold
    case BBlack = "\033[1;30m"; // Black
    case BRed = "\033[1;31m"; // Red
    case BGreen = "\033[1;32m"; // Green
    case BYellow = "\033[1;33m"; // Yellow
    case BBlue = "\033[1;34m"; // Blue
    case BPurple = "\033[1;35m"; // Purple
    case BCyan = "\033[1;36m"; // Cyan
    case BWhite = "\033[1;37m"; // White

    // Underline
    case UBlack = "\033[4;30m"; // Black
    case URed = "\033[4;31m"; // Red
    case UGreen = "\033[4;32m"; // Green
    case UYellow = "\033[4;33m"; // Yellow
    case UBlue = "\033[4;34m"; // Blue
    case UPurple = "\033[4;35m"; // Purple
    case UCyan = "\033[4;36m"; // Cyan
    case UWhite = "\033[4;37m"; // White

    // Background
    case On_Black = "\033[40m"; // Black
    case On_Red = "\033[41m"; // Red
    case On_Green = "\033[42m"; // Green
    case On_Yellow = "\033[43m"; // Yellow
    case On_Blue = "\033[44m"; // Blue
    case On_Purple = "\033[45m"; // Purple
    case On_Cyan = "\033[46m"; // Cyan
    case On_White = "\033[47m"; // White

    // High Intensity
    case IBlack = "\033[0;90m"; // Black
    case IRed = "\033[0;91m"; // Red
    case IGreen = "\033[0;92m"; // Green
    case IYellow = "\033[0;93m"; // Yellow
    case IBlue = "\033[0;94m"; // Blue
    case IPurple = "\033[0;95m"; // Purple
    case ICyan = "\033[0;96m"; // Cyan
    case IWhite = "\033[0;97m"; // White

    // Bold High Intensity
    case BIBlack = "\033[1;90m"; // Black
    case BIRed = "\033[1;91m"; // Red
    case BIGreen = "\033[1;92m"; // Green
    case BIYellow = "\033[1;93m"; // Yellow
    case BIBlue = "\033[1;94m"; // Blue
    case BIPurple = "\033[1;95m"; // Purple
    case BICyan = "\033[1;96m"; // Cyan
    case BIWhite = "\033[1;97m"; // White

    // High Intensity Backgrounds
    case On_IBlack = "\033[0;100m"; // Black
    case On_IRed = "\033[0;101m"; // Red
    case On_IGreen = "\033[0;102m"; // Green
    case On_IYellow = "\033[0;103m"; // Yellow
    case On_IBlue = "\033[0;104m"; // Blue
    case On_IPurple = "\033[0;105m"; // Purple (Note: using incorrect code from the original source)
    case On_ICyan = "\033[0;106m"; // Cyan
    case On_IWhite = "\033[0;107m"; // White
}
