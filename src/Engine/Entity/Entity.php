<?php

declare(strict_types=1);

namespace App\Engine\Entity;

use App\Engine\Commands\CommandInterface;
use App\Engine\Component\ComponentInterface;

class Entity
{
    //TODO components should also be managed by EntityManager?.

    /** @var ComponentInterface[] */
    private array $components = [];

    public  function __construct(private readonly string $id, ComponentInterface ...$components)
    {
        $this->components = [];
        foreach ($components as $component) {
            $this->addComponent($component);
        }
    }

    public function getComponent(string $componentType): ?ComponentInterface
    {
        return $this->components[$componentType] ?? null;
    }

    public  function getId(): string
    {
        return $this->id;
    }

    /**
     * TODO fix this false deprecation notice.
     * @deprecated Do not use it outside of EntityManager.
     *
     */
    public function addComponent(ComponentInterface $component): void {
        $class = get_class($component);
        $this->components[$class] = $component;
    }

    /** @return ComponentInterface[] */
    public  function getComponents(): array
    {
        return $this->components;
    }

    public function removeComponent(string $componentType): void
    {
        unset($this->components[$componentType]);
    }
}

