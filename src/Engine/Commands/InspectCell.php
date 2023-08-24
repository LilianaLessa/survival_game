<?php

declare(strict_types=1);

namespace App\Engine\Commands;

use App\Engine\Component\MapPosition;
use App\Engine\Component\PlayerCommandQueue;
use App\Engine\Entity\Entity;
use App\System\Direction;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\World\WorldManager;

class InspectCell implements InvokableCommandInterface
{
    //todo should different types of movement be declared here?
    // for example, climb, swim, walk, run and so on?
    public function __construct(
        private readonly WorldManager $world,
        private readonly MapPosition  $from,
        private readonly ?Direction   $direction,

    ) {
    }

    public function __invoke(PlayerCommandQueue $playerCommandQueue)
    {
        $coords = $this->calculateTargetCoordinates();

        $entities = $this->world->getEntityCollection(...$coords);

        $uiMessage = sprintf("%d entities found on %d,%d\n\n", count($entities), ...$coords);
        $i = 0;

        /* @var Entity $entity */
        foreach ($entities as $entityId => $entity) {
            $uiMessage .= sprintf("%d - %s\n", ++$i, $entityId);
            foreach ($entity->getComponents() as $component) {
                $uiMessage .= sprintf("\t%s\n", get_class($component));
            }
        }
        $uiMessage .= "\n\n";

        $biomeInfo = $this->world->getTerrainData()[$coords[0]][$coords[1]];

        [
            $height,
            $moisture,
            $heat,
            $biomePreset
        ] = array_values($biomeInfo);

        $uiMessage .= "Biome Info:\n";

        $uiMessage .= sprintf(
            "height: %s\n".
            "moisture: %s\n".
            "heat: %s\n".
            "biomePreset: %s\n" ,
            $height,
            $moisture,
            $heat,
            $biomePreset->getName()
        );

        Dispatcher::getInstance()->dispatch(new UiMessageEvent($uiMessage, $playerCommandQueue));
    }

    private function calculateTargetCoordinates(): array
    {
        $diff = match ($this->direction ?? null) {
            Direction::UP => [0,-1],
            Direction::DOWN => [0,1],
            Direction::LEFT => [-1,0],
            Direction::RIGHT => [1,0],
            default => [0,0],
        };

        return [
            $this->from->getX() + $diff[0],
            $this->from->getY() + $diff[1],
        ];
    }
}
