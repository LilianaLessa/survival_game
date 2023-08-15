<?php

declare(strict_types=1);

namespace App\System\AI\Behavior\EffectHandlers\Move;

use App\Engine\Commands\MoveEntity;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MovementQueue;
use App\Engine\Entity\Entity;
use App\Engine\Entity\EntityManager;
use App\System\AI\Behavior\BehaviorEffectConfig;
use App\System\AI\Behavior\BehaviorEffectParameterConfig;
use App\System\AI\Behavior\BehaviorEffectType;
use App\System\AI\Behavior\EffectHandlers\BehaviorEffectHandlerInterface;
use App\System\AI\Behavior\EffectHandlers\EffectParameterInterface;
use App\System\AI\Behavior\EffectHandlers\Move\Parameters\RandomTargetCoordinates;
use App\System\AI\Behavior\EffectHandlers\Move\Parameters\TargetCoordinatesInterface;
use App\System\AI\Behavior\EffectHandlers\Move\Parameters\TargetCoordinateTypes;
use App\System\Event\Dispatcher;
use App\System\Event\Event\UiMessageEvent;
use App\System\Helpers\Point2D;
use BlackScorp\Astar\Astar;
use BlackScorp\Astar\Grid;

class Move implements BehaviorEffectHandlerInterface
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }


    /**
     *  return a array of strings with the required parameter classes,
     *      so the engine can understand what should be injected on the handle method.
     *
     * @return string[]
     */
    public function getRequiredParameterClasses(): array
    {
        return [

        ];
    }

    //Todo maybe this Parameter should be an EffectParameterCollection, so
    //     It can be filtered in the same way of Entity Components are filtered.
    //     So, it would be up to the caller fill this collection with all types of parameters and their values.
    public function handle(Entity $targetEntity, EffectParameterInterface ...$effectParameter): void
    {
        Dispatcher::dispatch(
            new UiMessageEvent(
                sprintf(
                    "DEBUG-BEHAVIOR: handling move\n",
                )
            )
        );

        /** @var TargetCoordinatesInterface $movementType */
        $movementType = $effectParameter[0];

        /** @var MapPosition $mapPosition */
        $mapPosition = $targetEntity->getComponent(MapPosition::class);
        /** @var MovementQueue $movementQueue */
        $movementQueue = $targetEntity->getComponent(MovementQueue::class);

        if ($mapPosition && $movementQueue) {
            //todo get real info.
            $mapPassableBlocks = array_fill(0, 10, array_fill(0, 10, 0));

            $startPoint = $mapPosition->get();
            $targetPoint = $movementType->getTargetPoint($startPoint);

            $route = $this->calculateRoute(
                $startPoint,
                $targetPoint,
                $mapPassableBlocks
            );

            if (count($route) > 0) {
                $movementQueue->clear();

                forEach ($route as $coordinates) {
                    $movementQueue->add(new MoveEntity($coordinates));
                }

                $this->entityManager->updateEntityComponents(
                    $targetEntity->getId(),
                    $movementQueue
                );

                Dispatcher::dispatch(
                    new UiMessageEvent(
                        sprintf(
                            "Route triggered: %d,%d => %d,%d => %s\n",
                            ...[
                                ...$startPoint->toArray(),
                                ...$targetPoint->toArray(),
                                implode(
                                    ' ',
                                    array_map(
                                        fn (Point2D $p) => sprintf('%d,%d', ...$p->toArray()),
                                        $route
                                    )
                                )
                            ]
                        )
                    )
                );


            }
        }

        //todo how to react to changes on the map during the execution of the movement? just stop the movement?
    }

    public static function shouldHandle(BehaviorEffectType $effectType): bool
    {
        return $effectType === BehaviorEffectType::MOVE;
    }

    public static function buildEffectConfig(object $rawConfigData): BehaviorEffectConfig
    {
        return new BehaviorEffectConfig(
            BehaviorEffectType::MOVE,
            new BehaviorEffectParameterConfig(
                'target',
                TargetCoordinateTypes::tryFrom($rawConfigData->target ?? '')
            ),
            new BehaviorEffectParameterConfig(
                'minDistance',
                $rawConfigData->minDistance ?? null
            ),
            new BehaviorEffectParameterConfig(
                'maxDistance',
                $rawConfigData->maxDistance ?? null
            ),
        );
    }

    /** @return EffectParameterInterface[] */
    public static function buildEffectParameters(BehaviorEffectParameterConfig ...$effectParameterConfigs): array
    {
        $resultArray = [];

        foreach ($effectParameterConfigs as $effectParameterConfig) {
            $resultArray[$effectParameterConfig->getName()] = $effectParameterConfig->getValue();
        }

        $target = $resultArray['target'] ?? TargetCoordinateTypes::RANDOM_COORDINATES;

        return [
            match ($target) {
                TargetCoordinateTypes::RANDOM_COORDINATES => new RandomTargetCoordinates(
                    10, //todo these should come from world, somehow.
                    10,
                    $resultArray['minDistance'] ?? 2,
                    $resultArray['maxDistance'] ?? 2
                )
            }
        ];
    }

    /**
     * @return Point2D[]
     */
    private function calculateRoute(Point2D $start, Point2D $end, array $map): array
    {
        $grid = new Grid($map);

        $startPosition = $grid->getPoint($start->getY(), $start->getX());
        $endPosition = $grid->getPoint($end->getY(), $end->getX());
        $aStar = new Astar($grid);

        $nodes = $aStar->search($startPosition, $endPosition);
        array_shift($nodes);

        return array_map(fn ($n) => new Point2D($n->getX(), $n->getY()), $nodes);
    }
}
