<?php

declare(strict_types=1);

namespace App\System\Item;

class ItemBlueprint
{

    private bool $stackable = false;
    private int $stackSize = 1;

    private ?string $description = null;

    private ?string $shortDescription = null;
    private ?string $name = null;

    public function __construct(
        private readonly string $id,
        private readonly string $internalName,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getInternalName(): string
    {
        return $this->internalName;
    }

    public function isStackable(): bool
    {
        return $this->stackable;
    }

    public function setStackable(bool $stackable): self
    {
        $this->stackable = $stackable;
        return $this;
    }

    public function getStackSize(): int
    {
        return $this->stackSize;
    }

    public function setStackSize(int $stackSize): self
    {
        $this->stackSize = max(1, $stackSize);
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description ?? '<item description missing>';
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription ?? '<item short description missing>';;
    }

    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    public function getName(): string
    {
        return $this->name ?? $this->internalName;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
