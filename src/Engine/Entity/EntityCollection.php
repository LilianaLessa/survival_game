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
        $entity = $this[$entityId];

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
                $entity->removeComponent($componentClass);
                $allClasses = [
                    $componentClass,
                    ...$this->getComponentClasses($componentClass)
                ];

                foreach ($allClasses as $subClass) {
                    $this->components[$subClass][$entityId] = null;
                    unset($this->components[$subClass][$entityId]);
                }
            }
        }
    }

    /** @return ComponentInterface[] */
    public function getEntitiesWithComponents(string ...$componentClasses): array
    {
        $foundEntityComponents = [];
        foreach ($this as $entityId => $entity) {
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

    private function getComponentClasses(ComponentInterface|string $componentOrClassName): array
    {
        return [
            get_class($componentOrClassName),
            ...class_parents($componentOrClassName),
            ...class_parents($componentOrClassName),
            ...array_filter(class_implements($componentOrClassName), fn($c) => $c !== ComponentInterface::class),
        ];
    }
}
