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
use App\System\Player\PlayerFactory;
use App\System\Player\PlayerPresetLibrary;
use App\System\Screen\ClientScreenUpdater;
use App\System\Screen\Screen;
use App\System\Screen\ScreenUpdater;
use App\System\Server\Client\FixedUIClient;
use App\System\Server\Client\MainClient;
use App\System\Server\Client\MapClient;
use App\System\Server\Client\Network\ClientPool;
use App\System\Server\Client\UiMessageReceiverClient;
use App\System\Server\Client\UnblockingCliClient;
use App\System\Server\EventListener\DebugMessageServerEventListener;
use App\System\Server\EventListener\MapEntityRemovedServerEventListener;
use App\System\Server\EventListener\MapEntityUpdatedServerEventListener;
use App\System\Server\EventListener\PlayerUpdatedServerEventListener;
use App\System\Server\EventListener\UiMessageServerEventListener;
use App\System\Server\EventListener\UpdatePlayerCurrentTargetServerEventListener;
use App\System\Server\PacketHandlers\AttachClientHandler;
use App\System\Server\PacketHandlers\GameCommandHandler;
use App\System\Server\PacketHandlers\RegisterNewClientHandler;
use App\System\Server\PacketHandlers\RequestClientUuidHandler;
use App\System\Server\PacketHandlers\RequestMapDataHandler;
use App\System\Server\PacketHandlers\RequestPlayerDataHandler;
use App\System\Server\PacketHandlers\RequestPlayerSurroundingEntities;
use App\System\Server\PacketHandlers\SetPlayerNameHandler;
use App\System\Server\PacketHandlers\ShutdownSocketHandler;
use App\System\Server\ServerPresetLibrary;
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

    $services->set(ServerPresetLibrary::class, ServerPresetLibrary::class);
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


function registerGameInfrastructure(ServicesConfigurator $services): void
{
    $services->set(ScreenUpdater::class, ScreenUpdater::class)
        ->args([
            new Reference(EntityManager::class),
            new Reference(WorldManager::class),
            new Reference(WorldPresetLibrary::class),
        ]);


    $services->set(PlayerFactory::class, PlayerFactory::class)
        ->args([
            new Reference(WorldManager::class),
            new Reference(EntityManager::class),
        ]);
}

function registerClientPacketHandlers(ServicesConfigurator $services): void
{
    $services->set(RegisterNewClientHandler::class, RegisterNewClientHandler::class)->args([
        new Reference(ClientPool::class),
        new Reference(PlayerPresetLibrary::class),
        new Reference(PlayerFactory::class),
    ]);

    $services->set(GameCommandHandler::class, GameCommandHandler::class)->args([
        new Reference(ClientPool::class),
    ]);

    $services->set(ShutdownSocketHandler::class, ShutdownSocketHandler::class)->args([
        new Reference(ClientPool::class),
    ]);

    $services->set(AttachClientHandler::class, AttachClientHandler::class)->args([
        new Reference(ClientPool::class),
    ]);
    $services->set(RequestClientUuidHandler::class, RequestClientUuidHandler::class)->args([
        new Reference(ClientPool::class),
    ]);
    $services->set(RequestPlayerDataHandler::class, RequestPlayerDataHandler::class)->args([
        new Reference(ClientPool::class),
        new Reference(EntityManager::class),
        new Reference(PlayerUpdatedServerEventListener::class),
    ]);

    $services->set(RequestMapDataHandler::class, RequestMapDataHandler::class)->args([
        new Reference(ClientPool::class),
        new Reference(WorldManager::class),
    ]);

    $services->set(SetPlayerNameHandler::class, SetPlayerNameHandler::class)->args([
        new Reference(ClientPool::class),
        new Reference(EntityManager::class),
    ]);

    $services->set(RequestPlayerSurroundingEntities::class, RequestPlayerSurroundingEntities::class)->args([
        new Reference(ClientPool::class),
        new Reference(EntityManager::class),
        new Reference(WorldManager::class),
    ]);
}

function registerNetworkInfrastructure(ServicesConfigurator $services)
{
    registerClientPacketHandlers($services);

    registerClientTypes($services);

    $services->set(ClientPool::class, ClientPool::class)->args([
        new Reference(EntityManager::class),
    ]);
}

function registerClientTypes(ServicesConfigurator $services)
{
    $services->set(MainClient::class, MainClient::class)->args([
        new Reference(ServerPresetLibrary::class),
    ]);


    $services->set(UnblockingCliClient::class, UnblockingCliClient::class)->args([
        new Reference(ServerPresetLibrary::class),
    ]);

    $services->set(UiMessageReceiverClient::class, UiMessageReceiverClient::class)->args([
        new Reference(ServerPresetLibrary::class),
    ]);

    $services->set(FixedUIClient::class, FixedUIClient::class)->args([
        new Reference(ServerPresetLibrary::class),
    ]);

    $services->set(MapClient::class, MapClient::class)->args([
        new Reference(ServerPresetLibrary::class),
        new Reference(ClientScreenUpdater::class),
    ]);
}

function registerClientServices(ServicesConfigurator $services)
{
    $services->set(Screen::class, Screen::class)->args([
        new Reference(ConsoleColor::class),
    ]);

    $services->set(ClientScreenUpdater::class, ClientScreenUpdater::class)->args([
        new Reference(Screen::class),
    ]);
}

function registerServerEventListeners(ServicesConfigurator $services)
{
    $services->set(
        UiMessageServerEventListener::class,
        UiMessageServerEventListener::class
    )->args([
        new Reference(ClientPool::class),
    ]);

    $services->set(
        DebugMessageServerEventListener::class,
        DebugMessageServerEventListener::class
    )->args([
        new Reference(ClientPool::class),
    ]);

    $services->set(
        PlayerUpdatedServerEventListener::class,
        PlayerUpdatedServerEventListener::class
    )->args([
        new Reference(ClientPool::class),
        new Reference(EntityManager::class),
        new Reference(WorldManager::class),
    ]);

    $services->set(
        MapEntityUpdatedServerEventListener::class,
        MapEntityUpdatedServerEventListener::class
    )->args([
        new Reference(ClientPool::class),
        new Reference(WorldManager::class),
    ]);

    $services->set(
        MapEntityRemovedServerEventListener::class,
        MapEntityRemovedServerEventListener::class
    )->args([
        new Reference(ClientPool::class),
    ]);

    $services->set(
        UpdatePlayerCurrentTargetServerEventListener::class,
        UpdatePlayerCurrentTargetServerEventListener::class
    )->args([
        new Reference(ClientPool::class),
    ]);
}

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    registerManagers($services);

    registerGameInfrastructure($services);
    registerPresetLibraries($services);
    registerBehaviorEffectHandlers($services);
    registerHelpers($services);
    registerEngineServices($services);

    registerClientServices($services);

    registerNetworkInfrastructure($services);
    registerServerEventListeners($services);
};
