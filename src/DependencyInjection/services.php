<?php

declare(strict_types=1);

use App\Engine\Entity\EntityManager;
use App\Engine\System\BattleSystem;
use App\Engine\System\PlayerSpawner;
use App\System\AI\Behavior\EffectHandlers\Attack\Attack;
use App\System\AI\Behavior\EffectHandlers\IncreaseAggro\IncreaseAggro;
use App\System\AI\Behavior\EffectHandlers\Move\Move;
use App\System\AI\BehaviorPresetLibrary;
use App\System\Helpers\RouteService;
use App\System\Item\ItemPresetLibrary;
use App\System\Monster\MonsterPresetLibrary;
use App\System\Player\PlayerPresetLibrary;
use App\System\World\WorldManager;
use App\System\World\WorldPresetLibrary;
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
    $services->set(ItemPresetLibrary::class, ItemPresetLibrary::class);
    $services->set(WorldPresetLibrary::class, WorldPresetLibrary::class);
    $services->set(BehaviorPresetLibrary::class, BehaviorPresetLibrary::class);
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
}

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    registerManagers($services);

    registerPresetLibraries($services);
    registerBehaviorEffectHandlers($services);
    registerHelpers($services);
    registerEngineServices($services);
};
