<?php

declare(strict_types=1);

namespace App\System\Screen;

use App\System\World;
use function Amp\async;
use function Amp\delay;

class ScreenUpdater
{
    public function __construct(private readonly World $world, private readonly int $fps)
    {
    }

    public function intiScreenUpdate(): void
    {
        async(function (){
            $frameDurationInSeconds = 1 / max(1, $this->fps);
            do {
                system('clear');
                $this->world->draw();
                delay($frameDurationInSeconds);

            } while (1);
        });
    }
}
