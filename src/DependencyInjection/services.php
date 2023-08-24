<?php

declare(strict_types=1);

use App\Engine\Entity\EntityManager;
use App\Engine\System\BattleSystem;
use App\Engine\System\ColorEffectsSystem;
use App\Engine\System\ItemCollection\CollectItems;
use App\Engine\System\ItemCollection\EntityBehaviorSystem;
use App\Engine\System\MonsterSpawner;
use App\Engine\System\MovementApplier;
use App\Engine\System\PlayerController;
use App\Engine\System\PlayerSpawner;
use App\Engine\System\WorldActionApplier;
use App\Engine\System\WorldController;
use App\System\AI\Behavior\EffectHandlers\Attack\Attack;
use App\System\AI\Behavior\EffectHandlers\IncreaseAggro\IncreaseAggro;
use App\System\AI\Behavior\EffectHandlers\Move\Move;
use App\System\AI\BehaviorPresetLibrary;
use App\System\Biome\BiomeGeneratorService;
use App\System\Biome\BiomePresetLibrary;
use App\System\Helpers\PerlinNoiseGenerator;
use App\System\Helpers\RouteService;
use App\System\Item\ItemPresetLibrary;
use App\System\Monster\MonsterPresetLibrary;
use App\System\Monster\Spawner\MonsterSpawnerLibrary;
use App\System\Player\PlayerPresetLibrary;
use App\System\Screen\ScreenUpdater;
use App\System\World\WorldManager;
use App\System\World\WorldPresetLibrary;
use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\DependencyInjection\Reference;

function registerBehaviorEffectHandlers(ServicesConfigurator $services): void
{
    $services->set(Move::class, Move::class)
        ->args([
            new Reference(EntityManager::class),
            new Reference(RouteService::class),
        ]);

    $services->set(IncreaseAggro::class, IncreaseAggro::class)
        ->args([
            new Reference(EntityManager::class),
        ]);


    $services->set(Attack::class, Attack::class)
        ->args([
            new Reference(EntityManager::class),
        ]);
}

function registerPresetLibraries(ServicesConfigurator $services): void
{
    $services->set(PlayerPresetLibrary::class, PlayerPresetLibrary::class);
    $services->set(BiomePresetLibrary::class, BiomePresetLibrary::class);
    $services->set(ItemPresetLibrary::class, ItemPresetLibrary::class);
    $services->set(WorldPresetLibrary::class, WorldPresetLibrary::class);
    $services->set(BehaviorPresetLibrary::class, BehaviorPresetLibrary::class);
    $services->set(MonsterSpawnerLibrary::class, MonsterSpawnerLibrary::class);
    $services->set(MonsterPresetLibrary::class, MonsterPresetLibrary::class)
        ->args([
            new Reference(BehaviorPresetLibrary::class)
        ]);
}

function registerManagers(ServicesConfigurator $services): void
{
    $services->set(EntityManager::class, EntityManager::class);

    $services->set(WorldManager::class, WorldManager::class)
        ->args(
            [
                new Reference(EntityManager::class),
                new Reference(WorldPresetLibrary::class),
                new Reference(PlayerPresetLibrary::class),
                new Reference(ConsoleColor::class),
            ]
        )
    ;
}

function registerHelpers(ServicesConfigurator $services)
{
    $services->set(RouteService::class, RouteService::class)
        ->args([
            new Reference(WorldManager::class)
        ]);

    $services->set(PerlinNoiseGenerator::class, PerlinNoiseGenerator::class);

    $services->set(ConsoleColor::class, ConsoleColor::class);

    $services->set(BiomeGeneratorService::class, BiomeGeneratorService::class)
        ->args([
            new Reference(BiomePresetLibrary::class),
            new Reference(WorldManager::class),
            new Reference(PerlinNoiseGenerator::class),
        ]);
}


function registerEngineServices(ServicesConfigurator $services)
{
    $services->set(BattleSystem::class, BattleSystem::class)
        ->args([
            new Reference(EntityManager::class),
            new Reference(RouteService::class),
        ]);

    $services->set(PlayerSpawner::class, PlayerSpawner::class)
        ->args([
            new Reference(WorldManager::class),
            new Reference(PlayerPresetLibrary::class),
            new Reference(EntityManager::class),
        ]);

    $services->set(ColorEffectsSystem::class, ColorEffectsSystem::class)
        ->args([
            new Reference(EntityManager::class),
        ]);

    $services->set(WorldActionApplier::class, WorldActionApplier::class)
        ->args([
            new Reference(WorldManager::class),
            new Reference(EntityManager::class),
        ]);


    $services->set(CollectItems::class, CollectItems::class)
        ->args([
            new Reference(WorldManager::class),
            new Reference(EntityManager::class),
        ]);

    $services->set(MovementApplier::class, MovementApplier::class)
        ->args([
            new Reference(WorldManager::class),
            new Reference(EntityManager::class),
        ]);

    $services->set(EntityBehaviorSystem::class, EntityBehaviorSystem::class)
        ->args([
            new Reference(EntityManager::class),
            new Reference(WorldManager::class),
        ]);

    $services->set(PlayerController::class, PlayerController::class)
        ->args([
            new Reference(WorldManager::class),
            new Reference(EntityManager::class),
            new Reference(ItemPresetLibrary::class),
        ]);

    $services->set(WorldController::class, WorldController::class)
        ->args([
            new Reference(WorldManager::class),
        ]);

    $services->set(MonsterSpawner::class, MonsterSpawner::class)
        ->args([
            new Reference(WorldManager::class),
            new Reference(ItemPresetLibrary::class),
            new Reference(EntityManager::class),
            new Reference(MonsterPresetLibrary::class),
            new Reference(MonsterSpawnerLibrary::class),
            new Reference(BiomePresetLibrary::class),
        ]);
}


function registerInfrastructure(ServicesConfigurator $services): void
{
    $services->set(ScreenUpdater::class, ScreenUpdater::class)
        ->args([
            new Reference(EntityManager::class),
            new Reference(WorldManager::class),
            new Reference(WorldPresetLibrary::class),

        ]);
}


return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    registerManagers($services);

    registerInfrastructure($services);
    registerPresetLibraries($services);
    registerBehaviorEffectHandlers($services);
    registerHelpers($services);
    registerEngineServices($services);
};
