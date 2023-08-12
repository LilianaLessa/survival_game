<?php

declare(strict_types=1);

namespace App\Engine\Entity;

use App\Engine\Component\ComponentInterface;
use Ramsey\Uuid\Uuid;

class EntityManager
{
    /** @var Entity[] */
    private array $entities;
    private array $components;

    public function __construct()
    {
        $this->entities = [];
        $this->components = [];
    }

    public function getEntities(): array
    {
        return $this->entities;
    }

    public function getEntityById(string $entityId): ?Entity
    {
        return $this->entities[$entityId] ?? null;
    }

    public function createEntity(ComponentInterface ...$components): Entity
    {
        $entity = new Entity($this->generateEntityId(), ...$components);
        $this->addEntity($entity);

        return $entity;
    }

    public function removeComponentsFromEntity(string $entityId, ComponentInterface|string ...$componentClasses): void
    {
        $entity = $this->entities[$entityId];

        if ($entity) {
            foreach ($componentClasses as $componentClass) {
                $entity->removeComponent($componentClass);
                $allClasses = [
                    $componentClass,
                    ...$this->getComponentClasses($componentClass)
                ];

                foreach ($allClasses as $subClass) {
                    $this->components[$subClass][$entity->getId()] = null;
                    unset($this->components[$subClass][$entity->getId()]);
                }
            }
        }
    }

    public function updateEntityComponents(string $entityId, ComponentInterface ...$components): void
    {
        $entity = $this->getEntityById($entityId);
        if ($entity) {
            foreach ($components as $component) {
                $entity->addComponent($component);
            }

            $this->addEntity($entity);
        }
    }

    /** @return ComponentInterface[] */
    public function getEntitiesWithComponents(string ...$componentClasses): array
    {
        $foundEntityComponents = [];
        foreach ($this->entities as $entityId => $entity) {
            $foundComponents = [];
            foreach ($componentClasses as $componentClass) {
                $component = $this->components[$componentClass][$entityId] ?? null;
                if ($component === null) {
                    continue 2;
                }

                $foundComponents[] = $component;
            }

            $foundEntityComponents[$entityId] = $foundComponents;
        }

        return $foundEntityComponents;
    }


    public function removeEntity(string $entityId): void
    {
        $entity = $this->getEntityById($entityId);

        if ($entity) {
            //unset components;
            $this->removeComponentsFromEntity($entityId, ...$entity->getComponents());

            //unset entity;
            $this->entities[$entityId] = null;
            unset($this->entities[$entityId]);
            $entity = null;
        }
    }

    private function getComponentClasses(ComponentInterface|string $componentOrClassName): array
    {
        return [
            get_class($componentOrClassName),
            ...class_parents($componentOrClassName),
            ...class_parents($componentOrClassName),
            ...array_filter(class_implements($componentOrClassName), fn($c) => $c !== ComponentInterface::class),
        ];
    }

    private function generateEntityId(): string
    {
        return (Uuid::uuid4())->toString();
    }

    private function addEntity(Entity $entity)
    {
        $this->entities[$entity->getId()] = $entity;

        $components = $entity->getComponents();

        foreach ($components as $component) {
            $componentClasses = $this->getComponentClasses($component);

            foreach ($componentClasses as $componentClass) {
                $this->components[$componentClass][$entity->getId()] = $component;
            }
        }
    }
}
