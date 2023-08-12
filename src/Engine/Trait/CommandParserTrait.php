<?php

declare(strict_types=1);

namespace App\Engine\Trait;

use App\System\CommandPredicate;

trait CommandParserTrait
{
    private function extractCommand(string $command): array
    {
        $commandArray = explode(' ', $command);

        return [
            CommandPredicate::tryFrom(array_shift($commandArray)),
            $commandArray
        ];
    }
}
