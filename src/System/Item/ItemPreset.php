<?php

declare(strict_types=1);

namespace App\System\Item;

//todo maybe it should be called ItemPreset? To follow the convention of the AI's BehaviorPreset
use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\PresetDataType;

class ItemPreset extends AbstractPreset
{    private bool $stackable = false;
    private int $stackSize = 1;

    private ?string $description = null;

    private ?string $shortDescription = null;

    private ?ItemRarity $rarity = null;

    private ?ItemPrice $itemPrice = null;

    public function __construct(
         string $name,
    ) {
        parent::__construct(
            PresetDataType::ITEM_PRESET,
            $name
        );
    }

    public function getId(): string
    {
        return $this->id;
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

    public function getInGameName(): string
    {
        return $this->name ?? $this->internalName;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getRarity(): ItemRarity
    {
        return $this->rarity ?? ItemRarity::TRASH;
    }

    public function setRarity(?ItemRarity $rarity): self
    {
        $this->rarity = $rarity;
        return $this;
    }

    public function getItemPrice(): ItemPrice
    {
        return $this->itemPrice ?? new ItemPrice(0,0,0);
    }

    public function setItemPrice(?ItemPrice $itemPrice): self
    {
        $this->itemPrice = $itemPrice;
        return $this;
    }
}
