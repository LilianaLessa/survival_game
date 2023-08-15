<?php

declare(strict_types=1);

use App\Engine\Entity\EntityManager;
use App\System\AI\Behavior\EffectHandlers\Move\Move;
use App\System\AI\BehaviorPresetLibrary;
use App\System\Monster\MonsterPresetLibrary;
use App\System\World\WorldPresetLibrary;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\DependencyInjection\Reference;

function configureBehaviorEffectHandlers(ServicesConfigurator $services): void
{
    $services->set(Move::class, Move::class)
        ->args([
            new Reference(EntityManager::class),
            new Reference(WorldPresetLibrary::class),
        ]);
}

function configurePresetLibraries(ServicesConfigurator $services): void
{
    $services->set(WorldPresetLibrary::class, WorldPresetLibrary::class);
    $services->set(BehaviorPresetLibrary::class, BehaviorPresetLibrary::class);
    $services->set(MonsterPresetLibrary::class, MonsterPresetLibrary::class)
        ->args([
            new Reference(BehaviorPresetLibrary::class)
        ]);
}

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(EntityManager::class, EntityManager::class);

    configurePresetLibraries($services);
    configureBehaviorEffectHandlers($services);
};
