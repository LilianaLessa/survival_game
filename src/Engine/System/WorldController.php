<?php

declare(strict_types=1);

namespace App\Engine\System;

use App\Engine\Component\Fluid;
use App\Engine\Component\MapSymbol;
use App\Engine\Trait\CommandParserTrait;
use App\System\CommandPredicate;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\World;
use Symfony\Contracts\EventDispatcher\Event;

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

        $execute = match ($commandPredicate) {
            CommandPredicate::WORLD_SET_VIEW => function () use ($params) {
                $worldType = $params[0] ?? 'world';
                $worldViewType = match ($worldType) {
                    'fluid' => Fluid::class,
                    'world' => MapSymbol::class,
                    default => (function () use (&$worldType) { $worldType = 'world'; return null;})(),
                };

                $this->world->setDrawableClass($worldViewType);

                Dispatcher::getInstance()->dispatch(
                    new UiMessageEvent(
                        sprintf("World view mode set to %s\n", $worldType)
                    ),
                    UiMessageEvent::EVENT_NAME,
                );
            },
            default => null
        };

        $execute && $execute();
    }
}
