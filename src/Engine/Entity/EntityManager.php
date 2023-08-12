<?php

declare(strict_types=1);

namespace App\Engine\Entity;

use App\Engine\Component\ComponentInterface;
use Ramsey\Uuid\Uuid;

class EntityManager
{
    /** @var Entity[] */
    private EntityCollection $entityCollection;

    public function __construct()
    {
        $this->entityCollection = new EntityCollection();
    }

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
    }

    private function generateEntityId(): string
    {
        return (Uuid::uuid4())->toString();
    }

    private function addEntity(Entity $entity)
    {
        $this->entityCollection->addEntity($entity);
    }
}
