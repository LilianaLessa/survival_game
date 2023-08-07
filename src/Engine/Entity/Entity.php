<?php

declare(strict_types=1);

namespace App\Engine\Entity;

use App\Engine\Commands\CommandInterface;
use App\Engine\Component\ComponentInterface;

class Entity
{
    /** @var ComponentInterface[] */
    private array $components = [];

    /** @var CommandInterface[] */
    private array $commands = [];

    public function __construct(private readonly int $id, ComponentInterface ...$components)
    {
        $this->components = [];
        foreach ($components as $component) {
            $this->addComponent($component);
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function addComponent(ComponentInterface $component): void {
        $class = get_class($component);
        $this->components[$class] = $component;
    }

    public function getComponent(string $componentType): ?ComponentInterface
    {
        return $this->components[$componentType] ?? null;
    }

    public function removeComponent(string $componentType): void
    {
        unset($this->components[$componentType]);
    }

    public function addCommand(CommandInterface $command): self
    {
        $this->commands[] = $command;

        return $this;
    }

    /** @return CommandInterface[] */
    public function getCommands(): array
    {
        return $this->commands;
    }

    public function setCommands(CommandInterface ...$commands): self
    {
        $this->commands = $commands;

        return $this;
    }
}
