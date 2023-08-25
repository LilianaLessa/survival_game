<?php

declare(strict_types=1);

namespace App\Engine\Entity;

use App\Engine\Component\ComponentInterface;
use App\Engine\Component\MapPosition;
use App\Engine\Component\MapViewPort;
use App\Engine\Component\PlayerCommandQueue;
use App\System\Event\Dispatcher;
use App\System\Event\Event\MapEntityRemoved;
use App\System\Event\Event\MapEntityUpdated;
use App\System\Event\Event\PlayerUpdated;
use Ramsey\Uuid\Uuid;

class EntityManager
{
    /** @var Entity[] */
    private EntityCollection $entityCollection;

    public function __construct()
    {
        $this->entityCollection = new EntityCollection();
    }

    /** @return Entity[] */
    public function getEntityCollection(): EntityCollection
    {
        return $this->entityCollection;
    }

    public function getEntityById(string $entityId): ?Entity
    {
        return $this->entityCollection[$entityId] ?? null;
    }

    public function createEntity(ComponentInterface ...$components): Entity
    {
        $entity = new Entity($this->generateEntityId(), ...$components);
        $this->addEntity($entity);

        return $entity;
    }

    public function removeComponentsFromEntity(string $entityId, ComponentInterface|string ...$componentClasses): void
    {
        $this->entityCollection->removeComponentsFromEntity($entityId, ...$componentClasses);

        $entity = $this->getEntityById($entityId);

        $this->sendClientUpdates($entity);
    }

    public function updateEntityComponents(string $entityId, ComponentInterface ...$components): ?Entity
    {
        $entity = $this->getEntityById($entityId);
        if ($entity) {
            foreach ($components as $component) {
                $entity->addComponent($component);
            }

            $this->addEntity($entity);
        }

        $this->sendClientUpdates($entity);

        return $entity;
    }

    public function entityHasComponent(string $entityId, string $componentClass): bool
    {
        return $this->entityCollection->entityHasComponent($entityId, $componentClass);
    }

    /** @return ComponentInterface[] */
    public function getEntitiesWithComponents(string ...$componentClasses): array
    {
       return $this->entityCollection->getEntitiesWithComponents(...$componentClasses);
    }

    public function removeEntity(string $entityId): void
    {
        $this->entityCollection->removeEntity($entityId);

        Dispatcher::dispatch(new MapEntityRemoved($entityId));
    }

    public function getComponentFromEntityId(string $entityId, string $componentClass): ?ComponentInterface
    {
        return $this->entityCollection[$entityId]->getComponent($componentClass);
    }

    private function generateEntityId(): string
    {
        return (Uuid::uuid4())->toString();
    }

    private function addEntity(Entity $entity)
    {
        $this->entityCollection->addEntity($entity);

        $this->sendClientUpdates($entity);
    }

    private function sendClientUpdates(?Entity $entity): void
    {
        /** @var PlayerCommandQueue $playerCommandQueue */
        /** @var MapPosition $mapPosition */
        /** @var MapViewPort $playerViewport */
        [
            $playerCommandQueue,
            $mapPosition
        ] = $entity?->explode(
            PlayerCommandQueue::class,
            MapPosition::class,
            MapViewPort::class
        ) ?? [null,null];

        $playerCommandQueue && Dispatcher::dispatch(new PlayerUpdated($playerCommandQueue));

        $mapPosition && Dispatcher::dispatch(new MapEntityUpdated($entity));
    }
}
