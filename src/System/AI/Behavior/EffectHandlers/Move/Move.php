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
use App\System\AI\Behavior\EffectHandlers\Move\Parameters\RandomTargetBiomaCoordinates;
use App\System\AI\Behavior\EffectHandlers\Move\Parameters\RandomTargetCoordinates;
use App\System\AI\Behavior\EffectHandlers\Move\Parameters\TargetCoordinatesInterface;
use App\System\AI\Behavior\EffectHandlers\Move\Parameters\TargetCoordinateTypes;
use App\System\Helpers\RouteService;
use App\System\Kernel;
use App\System\World\WorldManager;
use App\System\World\WorldPreset;
use App\System\World\WorldPresetLibrary;

class Move implements BehaviorEffectHandlerInterface
{
    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly RouteService $routeService,
    ){
    }

    /**
     * todo
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
        /** @var TargetCoordinatesInterface $movementType */
        $movementType = $effectParameter[0];

        /** @var MapPosition $mapPosition */
        $mapPosition = $targetEntity->getComponent(MapPosition::class);
        /** @var MovementQueue $movementQueue */
        $movementQueue = $targetEntity->getComponent(MovementQueue::class);

        if ($mapPosition && $movementQueue) {

            $startPoint = $mapPosition->get();
            $targetPoint = $movementType->getTargetPoint($startPoint, $targetEntity);

            $route = $this->routeService->calculateRoute(
                $startPoint,
                $targetPoint
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
    public static function buildEffectParameters(
        WorldManager $worldManager,
        BehaviorEffectParameterConfig ...$effectParameterConfigs
    ): array {
        $resultArray = [];

        foreach ($effectParameterConfigs as $effectParameterConfig) {
            $resultArray[$effectParameterConfig->getName()] = $effectParameterConfig->getValue();
        }

        $target = $resultArray['target'] ?? TargetCoordinateTypes::RANDOM_COORDINATES;

        //todo get real info.
        /** @var WorldPreset $worldPreset */
        $worldPreset = Kernel::getContainer()->get(WorldPresetLibrary::class)->getDefaultWorldPreset();

        return [
            match ($target) {
                TargetCoordinateTypes::RANDOM_COORDINATES => new RandomTargetCoordinates(
                    $worldPreset->getMapWidth(),
                    $worldPreset->getMapHeight(),
                    $resultArray['minDistance'] ?? 2,
                    $resultArray['maxDistance'] ?? 2
                ),
                TargetCoordinateTypes::RANDOM_BIOMA_COORDINATES => new RandomTargetBiomaCoordinates(
                    $worldManager,
                    $worldPreset->getMapWidth(),
                    $worldPreset->getMapHeight(),
                    $resultArray['minDistance'] ?? 2,
                    $resultArray['maxDistance'] ?? 2
                )
            }
        ];
    }
}
