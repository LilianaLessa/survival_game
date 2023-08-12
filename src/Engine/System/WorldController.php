<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\Fluid;
use App\Engine\Component\MapSymbol;
use App\Engine\Trait\CommandParserTrait;
use App\System\CommandPredicate;
use App\System\World;

class WorldController implements ReceiverSystemInterface
{
    use CommandParserTrait;

    public function __construct(private readonly World $world)
    {
    }

    public function parse(string $rawCommand): void
    {
        /**
         * @var CommandPredicate $commandPredicate
         * @var array $params
         */
        [ $commandPredicate, $params ] = $this->extractCommand($rawCommand);

        $this->parseWorldViewChange($commandPredicate, $params);
    }

    private function parseWorldViewChange(?CommandPredicate $commandPredicate, array $params): void
    {
        $worldViewType = match ($commandPredicate) {
            CommandPredicate::WORLD_SET_VIEW => match($params[0] ?? null) {
                'fluid' => Fluid::class,
                'world' => MapSymbol::class,
                default => null,
            },
            default => null,
        };

        $this->world->setDrawableClass($worldViewType);
    }
}
