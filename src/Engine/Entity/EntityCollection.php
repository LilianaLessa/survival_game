<?php

declare(strict_types=1);

namespace App\Engine\Entity;

use App\Engine\Component\ComponentInterface;

class EntityCollection extends \ArrayObject
{
   /** @var ComponentInterface[] */
    private array $components;

    /** Entity[] */
    public function getEntities(): array
    {
        return [...$this];
    }

    public function addEntity(Entity $entity)
    {
        $this[$entity->getId()] = $entity;

        $components = $entity->getComponents();

        foreach ($components as $component) {
            $componentClasses = $this->getComponentClasses($component);

            foreach ($componentClasses as $componentClass) {
                $this->components[$componentClass][$entity->getId()] = $component;
            }
        }
    }

    public function removeEntity(string $entityId): void
    {
        $entity = $this[$entityId] ?? null;

        if ($entity) {
            //unset components;
            $this->removeComponentsFromEntity($entityId, ...$entity->getComponents());

            //unset entity;
            $this->entityCollection[$entityId] = null;
            unset($this->entityCollection[$entityId]);
            $entity = null;
        }
    }

    public function removeComponentsFromEntity(string $entityId, ComponentInterface|string ...$componentClasses): void
    {
        /** @var Entity $entity */
        $entity = $this[$entityId];

        if ($entity) {
            foreach ($componentClasses as $componentClass) {
                $classToRemove = is_string($componentClass) ? $componentClass : get_class($componentClass);
                $entity->removeComponent($classToRemove);
                $allClasses = [
                    $classToRemove,
                    ...$this->getComponentClasses($componentClass)
                ];

                foreach ($allClasses as $subClass) {
                    if ($this->components[$subClass][$entityId] ?? null) {
                        $this->components[$subClass][$entityId] = null;
                        unset($this->components[$subClass][$entityId]);
                    }
                }
            }
        }
    }

    /** @return ComponentInterface[] */
    public function getEntitiesWithComponents(string ...$componentClasses): array
    {
        $entityIds = array_intersect(
            ...array_map(
                fn ($c) => array_keys($this->components[$c] ?? []),
                $componentClasses
            )
        );

        $foundEntityComponents = [];
        foreach ($entityIds as $entityId) {
            $foundComponents = [];
            foreach ($componentClasses as $componentClass) {
                $foundComponents[] = $this->components[$componentClass][$entityId];
            }

            $foundEntityComponents[$entityId] = $foundComponents;
        }

        return $foundEntityComponents;
    }

    public function entityHasComponent(string $entityId, string $componentClass): bool
    {
        return ($this->components[$componentClass][$entityId] ?? null) !== null;
    }

    private function getComponentClasses(ComponentInterface|string $componentOrClassName): array
    {
        $targetClass = is_string($componentOrClassName) ? $componentOrClassName: get_class($componentOrClassName);

        return [
            $targetClass,
            ...class_parents($componentOrClassName),
            ...array_filter(class_implements($componentOrClassName), fn($c) => $c !== ComponentInterface::class),
        ];
    }
}
